<?php
if (is_post()) {
    csrf_verify();
    $id  = (int)($_POST['id'] ?? 0);
    $act = (string)($_POST['act'] ?? '');
    $p   = row('SELECT id FROM products WHERE id = ?', [$id]);
    if (!$p) {
        flash('error', 'Produk tidak ditemukan.');
    } else {
        switch ($act) {
            case 'approve':   q("UPDATE products SET status = 'published' WHERE id = ?", [$id]); flash('success', 'Produk disetujui dan tayang.'); break;
            case 'reject':    q("UPDATE products SET status = 'rejected' WHERE id = ?", [$id]); flash('success', 'Produk ditolak.'); break;
            case 'feature':   q('UPDATE products SET is_featured = 1 WHERE id = ?', [$id]); flash('success', 'Produk dijadikan pilihan editor.'); break;
            case 'unfeature': q('UPDATE products SET is_featured = 0 WHERE id = ?', [$id]); flash('success', 'Produk dilepas dari pilihan editor.'); break;
            case 'delete':    delete_product($id); flash('success', 'Produk dihapus.'); break;
            default: flash('error', 'Aksi tidak dikenal.');
        }
    }
    redirect('admin/produk' . (!empty($_POST['qs']) ? '?' . ltrim((string)$_POST['qs'], '?') : ''));
}

$q  = trim((string)($_GET['q'] ?? ''));
$st = (string)($_GET['status'] ?? '');
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(p.title LIKE ? OR u.store_name LIKE ? OR u.username LIKE ?)';
    array_push($params, $like, $like, $like);
}
if (isset(status_labels()[$st])) {
    $where[] = 'p.status = ?';
    $params[] = $st;
} else {
    $st = '';
}
$w     = implode(' AND ', $where);
$total = (int)val('SELECT COUNT(*) FROM products p JOIN users u ON u.id = p.seller_id WHERE ' . $w, $params);
$pg    = paginate($total, 20, (int)($_GET['page'] ?? 1));
$items = rows('SELECT p.*, u.store_name, u.username, u.name AS seller_name, u.status AS seller_status
               FROM products p JOIN users u ON u.id = p.seller_id WHERE ' . $w .
               " ORDER BY (p.status = 'pending') DESC, p.id DESC LIMIT " . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'], $params);
$counts = [];
foreach (rows('SELECT status, COUNT(*) AS n FROM products GROUP BY status') as $r) $counts[$r['status']] = (int)$r['n'];
$qs = http_build_query(array_filter(['q' => $q, 'status' => $st, 'page' => $pg['page'] > 1 ? $pg['page'] : null]));
$area = 'admin'; $active = 'products'; $title = 'Moderasi produk';
render('admin/products', compact('items', 'pg', 'total', 'q', 'st', 'qs', 'counts', 'area', 'active', 'title'), 'dash');
