<?php
$u  = current_user();
$id = 0;
if ($action === 'edit') {
    $id = (int)($seg[3] ?? 0);
    if ($id <= 0) abort(404);
}
$product = null;
$gallery = [];
if ($id) {
    $product = row('SELECT * FROM products WHERE id = ?', [$id]);
    if (!$product || ((int)$product['seller_id'] !== (int)$u['id'] && $u['role'] !== 'admin')) abort(404);
    $gallery = rows('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$id]);
}
$cats = all_categories();

$flashOk = flash_ready();
$f = [
    'title' => '', 'category_id' => 0, 'type' => 'software', 'price' => 0, 'short_desc' => '',
    'description' => '', 'features' => '', 'tech_stack' => '', 'demo_url' => '', 'demo_note' => '',
    'compare_price' => 0, 'flash_price' => 0, 'flash_start' => '', 'flash_end' => '',
];
if ($product) {
    foreach ($f as $k => $_) $f[$k] = $product[$k] ?? $f[$k];
    $f['category_id'] = (int)$product['category_id'];
    $f['flash_start'] = dt_local_value($product['flash_start'] ?? null);
    $f['flash_end']   = dt_local_value($product['flash_end'] ?? null);
}

$errors = [];
if (is_post()) {
    csrf_verify();
    foreach (['title', 'short_desc', 'description', 'features', 'tech_stack', 'demo_url', 'demo_note'] as $k) {
        $f[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $f['category_id'] = (int)($_POST['category_id'] ?? 0);
    $f['type']  = (string)($_POST['type'] ?? 'software');
    $f['price'] = money_int($_POST['price'] ?? '0');
    if ($flashOk) {
        $f['compare_price'] = money_int($_POST['compare_price'] ?? '0');
        $f['flash_price']   = money_int($_POST['flash_price'] ?? '0');
        $f['flash_start']   = trim((string)($_POST['flash_start'] ?? ''));
        $f['flash_end']     = trim((string)($_POST['flash_end'] ?? ''));
    }
    $submit     = ($_POST['submit_action'] ?? 'publish') === 'draft' ? 'draft' : 'publish';

    $len = function ($s) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); };
    if ($len($f['title']) < 3 || $len($f['title']) > 150) $errors[] = 'Nama produk 3-150 karakter.';
    if (!isset(product_types()[$f['type']])) $errors[] = 'Jenis produk tidak valid.';
    if ($f['category_id'] !== 0 && !in_array($f['category_id'], array_map('intval', array_column($cats, 'id')), true)) $errors[] = 'Kategori tidak valid.';
    if ($len($f['short_desc']) > 255) $errors[] = 'Ringkasan maksimal 255 karakter.';
    if ($submit === 'publish' && $len($f['description']) < 20) $errors[] = 'Deskripsi minimal 20 karakter agar bisa diterbitkan.';
    if ($len($f['tech_stack']) > 255) $errors[] = 'Teknologi maksimal 255 karakter.';
    if ($len($f['demo_note']) > 500) $errors[] = 'Info akun demo maksimal 500 karakter.';
    if ($f['demo_url'] !== '' && !valid_demo_url($f['demo_url'])) $errors[] = 'URL demo harus diawali http:// atau https:// dan berupa alamat yang valid.';

    // ---- Harga coret & flash sale ----
    $dbFlashStart = null;
    $dbFlashEnd   = null;
    if ($flashOk) {
        if ($f['compare_price'] > 0) {
            if ($f['price'] <= 0) $errors[] = 'Isi Harga terlebih dahulu sebelum memakai harga coret.';
            elseif ($f['compare_price'] <= $f['price']) $errors[] = 'Harga coret harus lebih tinggi dari harga jual.';
        }
        $hasFlashTime = $f['flash_start'] !== '' || $f['flash_end'] !== '';
        if ($f['flash_price'] > 0) {
            $dbFlashStart = parse_local_dt($f['flash_start']);
            $dbFlashEnd   = parse_local_dt($f['flash_end']);
            if ($f['price'] <= 0) {
                $errors[] = 'Isi Harga normal terlebih dahulu sebelum membuat flash sale.';
            } elseif ($f['flash_price'] >= $f['price']) {
                $errors[] = 'Harga flash sale harus lebih rendah dari harga normal.';
            }
            if ($f['flash_start'] !== '' && $dbFlashStart === null) $errors[] = 'Waktu mulai flash sale tidak valid.';
            if ($dbFlashEnd === null) {
                $errors[] = 'Waktu berakhir flash sale wajib diisi dan harus valid.';
            } else {
                // Input datetime-local tidak menyimpan detik, jadi bandingkan sampai menit saja.
                $unchanged = $product
                    && (int)$product['flash_price'] === $f['flash_price']
                    && substr((string)$product['flash_end'], 0, 16) === substr($dbFlashEnd, 0, 16);
                if (!$unchanged && strtotime($dbFlashEnd) <= time()) $errors[] = 'Waktu berakhir flash sale harus di masa depan.';
                if ($dbFlashStart !== null && strtotime($dbFlashEnd) <= strtotime($dbFlashStart)) $errors[] = 'Waktu berakhir harus setelah waktu mulai.';
                $from = $dbFlashStart !== null ? strtotime($dbFlashStart) : time();
                if (strtotime($dbFlashEnd) - $from > 90 * 86400) $errors[] = 'Durasi flash sale maksimal 90 hari.';
            }
        } elseif ($hasFlashTime) {
            $errors[] = 'Isi harga flash sale, atau kosongkan waktu mulai dan berakhirnya.';
        }
    }

    $delIds = array_map('intval', (array)($_POST['del_img'] ?? []));
    $keep   = 0;
    foreach ($gallery as $g) if (!in_array((int)$g['id'], $delIds, true)) $keep++;
    $newGallery = array_values(array_filter(files_array('gallery'), function ($x) {
        return ($x['error'] ?? 4) !== UPLOAD_ERR_NO_FILE;
    }));
    if ($keep + count($newGallery) > 4) $errors[] = 'Galeri maksimal 4 gambar tambahan.';

    $savedNow = [];
    $newThumb = null;
    $newGalleryFiles = [];
    if (!$errors) {
        if (!empty($_FILES['thumbnail'])) {
            $r = save_image($_FILES['thumbnail']);
            if ($r['ok']) { $newThumb = $r['file']; $savedNow[] = $r['file']; }
            elseif (empty($r['none'])) $errors[] = 'Gambar utama: ' . $r['error'];
        }
        foreach ($newGallery as $gf) {
            $r = save_image($gf);
            if ($r['ok']) { $newGalleryFiles[] = $r['file']; $savedNow[] = $r['file']; }
            elseif (empty($r['none'])) $errors[] = 'Galeri: ' . $r['error'];
        }
        if ($errors) {
            foreach ($savedNow as $sf) delete_upload($sf);
        }
    }

    if (!$errors) {
        $moderate = setting('moderate_products', '1') === '1';
        if ($submit === 'draft') {
            $status = 'draft';
        } elseif ($u['role'] === 'admin' || !$moderate) {
            $status = 'published';
        } else {
            $status = ($product && $product['status'] === 'published') ? 'published' : 'pending';
        }
        $cat = $f['category_id'] ?: null;

        if ($product) {
            $sql = 'UPDATE products SET category_id=?, title=?, type=?, price=?, short_desc=?, description=?, features=?, tech_stack=?, demo_url=?, demo_note=?, status=?';
            $par = [$cat, $f['title'], $f['type'], $f['price'], $f['short_desc'], $f['description'], $f['features'], $f['tech_stack'], $f['demo_url'], $f['demo_note'], $status];
            if ($flashOk) {
                $sql .= ', compare_price=?, flash_price=?, flash_start=?, flash_end=?';
                array_push($par, $f['compare_price'], $f['flash_price'], $f['flash_price'] > 0 ? $dbFlashStart : null, $f['flash_price'] > 0 ? $dbFlashEnd : null);
            }
            if ($newThumb) { $sql .= ', thumbnail=?'; $par[] = $newThumb; }
            $sql .= ' WHERE id=?';
            $par[] = $product['id'];
            q($sql, $par);
            if ($newThumb) delete_upload($product['thumbnail']);
            $pid = (int)$product['id'];
            foreach ($gallery as $g) {
                if (in_array((int)$g['id'], $delIds, true)) {
                    delete_upload($g['filename']);
                    q('DELETE FROM product_images WHERE id = ? AND product_id = ?', [$g['id'], $pid]);
                }
            }
        } else {
            $slug = unique_slug('products', slugify($f['title']));
            q('INSERT INTO products (seller_id, category_id, title, slug, type, price, short_desc, description, features, tech_stack, demo_url, demo_note, thumbnail, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [$u['id'], $cat, $f['title'], $slug, $f['type'], $f['price'], $f['short_desc'], $f['description'], $f['features'], $f['tech_stack'], $f['demo_url'], $f['demo_note'], $newThumb, $status]);
            $pid = last_id();
            if ($flashOk && ($f['compare_price'] > 0 || $f['flash_price'] > 0)) {
                q('UPDATE products SET compare_price=?, flash_price=?, flash_start=?, flash_end=? WHERE id=?',
                    [$f['compare_price'], $f['flash_price'], $f['flash_price'] > 0 ? $dbFlashStart : null, $f['flash_price'] > 0 ? $dbFlashEnd : null, $pid]);
            }
        }
        foreach ($newGalleryFiles as $i => $gfile) {
            q('INSERT INTO product_images (product_id, filename, sort_order) VALUES (?,?,?)', [$pid, $gfile, $i + 1]);
        }

        if ($status === 'draft') flash('success', 'Draft disimpan.');
        elseif ($status === 'pending') flash('info', 'Produk dikirim dan menunggu review admin. Akan tayang setelah disetujui.');
        else flash('success', 'Produk berhasil disimpan dan tayang.');
        redirect($u['role'] === 'admin' && (int)($product['seller_id'] ?? $u['id']) !== (int)$u['id'] ? 'admin/produk' : 'dashboard/produk');
    }
}

$waOk = valid_wa(normalize_wa((string)$u['whatsapp']));
$area = 'seller'; $active = 'products';
$title = $product ? 'Edit produk' : 'Tambah produk';
$serverNow = date('Y-m-d\TH:i');
render('dash/product_form', compact('product', 'gallery', 'cats', 'f', 'errors', 'waOk', 'area', 'active', 'title', 'flashOk', 'serverNow'), 'dash');
