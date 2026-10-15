<?php
$p = row(product_base_sql() . ' WHERE p.slug = ? LIMIT 1', [$slug]);
if (!$p || $p['demo_url'] === '') abort(404, 'Demo tidak tersedia.');

$me = current_user();
$isOwner = $me && (int)$me['id'] === (int)$p['seller_id'];
if ($p['status'] !== 'published' && !($me && ($me['role'] === 'admin' || $isOwner))) abort(404, 'Demo tidak tersedia.');

if ($p['status'] === 'published' && !$isOwner && empty($_SESSION['d'][$p['id']])) {
    q('UPDATE products SET demo_clicks = demo_clicks + 1 WHERE id = ?', [$p['id']]);
    $_SESSION['d'][$p['id']] = 1;
}
render('demo', compact('p'), 'blank');
