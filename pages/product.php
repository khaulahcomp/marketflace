<?php
$p = row(product_base_sql() . ' WHERE p.slug = ? LIMIT 1', [$slug]);
if (!$p) abort(404, 'Produk tidak ditemukan.');

$me      = current_user();
$canView = $p['status'] === 'published' || ($me && ($me['role'] === 'admin' || (int)$me['id'] === (int)$p['seller_id']));
if (!$canView) abort(404, 'Produk tidak ditemukan.');
$isOwner = $me && (int)$me['id'] === (int)$p['seller_id'];

if ($p['status'] === 'published' && !$isOwner && empty($_SESSION['v'][$p['id']])) {
    q('UPDATE products SET views = views + 1 WHERE id = ?', [$p['id']]);
    $_SESSION['v'][$p['id']] = 1;
}

$gallery = rows('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$p['id']]);
$related = [];
if ($p['category_id']) {
    $related = rows(product_base_sql() . " WHERE p.status = 'published' AND p.category_id = ? AND p.id <> ? ORDER BY RAND() LIMIT 4", [$p['category_id'], $p['id']]);
}
$sellerCount = (int)val("SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = 'published'", [$p['seller_id']]);

render('product', compact('p', 'gallery', 'related', 'isOwner', 'sellerCount'));
