<?php
$me = current_user();

if (is_post()) {
    csrf_verify();
    $id  = (int)($_POST['id'] ?? 0);
    $act = (string)($_POST['act'] ?? '');
    $target = row('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$target) {
        flash('error', 'Pengguna tidak ditemukan.');
    } elseif ((int)$target['id'] === (int)$me['id']) {
        flash('error', 'Anda tidak dapat mengubah akun Anda sendiri dari halaman ini.');
    } else {
        switch ($act) {
            case 'activate': q("UPDATE users SET status = 'active' WHERE id = ?", [$id]); flash('success', 'Akun diaktifkan.'); break;
            case 'ban':      q("UPDATE users SET status = 'banned' WHERE id = ?", [$id]); flash('success', 'Akun dinonaktifkan. Produknya otomatis tidak tampil.'); break;
            case 'make_admin':  q("UPDATE users SET role = 'admin' WHERE id = ?", [$id]); flash('success', 'Pengguna dijadikan admin.'); break;
            case 'make_seller': q("UPDATE users SET role = 'seller' WHERE id = ?", [$id]); flash('success', 'Pengguna dijadikan penjual biasa.'); break;
            case 'delete':
                delete_user($id);
                flash('success', 'Pengguna dan seluruh produknya dihapus.');
                break;
            default: flash('error', 'Aksi tidak dikenal.');
        }
    }
    redirect('admin/pengguna' . (!empty($_POST['qs']) ? '?' . ltrim((string)$_POST['qs'], '?') : ''));
}

$q  = trim((string)($_GET['q'] ?? ''));
$st = (string)($_GET['status'] ?? '');
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.store_name LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}
if (in_array($st, ['active', 'pending', 'banned'], true)) {
    $where[] = 'u.status = ?';
    $params[] = $st;
} else {
    $st = '';
}
$w     = implode(' AND ', $where);
$total = (int)val('SELECT COUNT(*) FROM users u WHERE ' . $w, $params);
$pg    = paginate($total, 20, (int)($_GET['page'] ?? 1));
$items = rows('SELECT u.*, (SELECT COUNT(*) FROM products p WHERE p.seller_id = u.id) AS n_products
               FROM users u WHERE ' . $w
               . ' ORDER BY u.id DESC LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'], $params);
$qs = http_build_query(array_filter(['q' => $q, 'status' => $st, 'page' => $pg['page'] > 1 ? $pg['page'] : null]));
$area = 'admin'; $active = 'users'; $title = 'Pengguna';
render('admin/users', compact('items', 'pg', 'total', 'q', 'st', 'qs', 'me', 'area', 'active', 'title'), 'dash');
