<?php
$seller = row("SELECT * FROM users WHERE username = ? AND status = 'active'", [$username]);
if (!$seller) abort(404, 'Toko tidak ditemukan.');
$items = rows(product_base_sql() . " WHERE p.status = 'published' AND p.seller_id = ? ORDER BY p.created_at DESC LIMIT 60", [$seller['id']]);
render('store', compact('seller', 'items'));
