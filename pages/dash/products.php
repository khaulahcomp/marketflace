<?php
$u = current_user();

if (($action ?? '') === 'hapus') {
    if (!is_post()) redirect('dashboard/produk');
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $p  = row('SELECT id, seller_id FROM products WHERE id = ?', [$id]);
    if ($p && ((int)$p['seller_id'] === (int)$u['id'] || $u['role'] === 'admin')) {
        delete_product($id);
        flash('success', 'Produk dihapus.');
    }
    redirect(($_POST['back'] ?? '') === 'admin' && $u['role'] === 'admin' ? 'admin/produk' : 'dashboard/produk');
}

$st = (string)($_GET['status'] ?? '');
$where = ['seller_id = ?'];
$params = [$u['id']];
if (isset(status_labels()[$st])) {
    $where[] = 'status = ?';
    $params[] = $st;
} else {
    $st = '';
}
$items = rows('SELECT * FROM products WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC', $params);
$counts = [];
foreach (rows('SELECT status, COUNT(*) AS n FROM products WHERE seller_id = ? GROUP BY status', [$u['id']]) as $r) {
    $counts[$r['status']] = (int)$r['n'];
}
$area = 'seller'; $active = 'products'; $title = 'Produk saya';
$top_actions = '<a class="btn btn-primary btn-sm" href="' . e(url('dashboard/produk/tambah')) . '">+ Tambah produk</a>';
render('dash/products', compact('items', 'st', 'counts', 'area', 'active', 'title', 'top_actions'), 'dash');
