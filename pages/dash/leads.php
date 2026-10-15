<?php
$u = current_user();
$total = (int)val('SELECT COUNT(*) FROM leads WHERE seller_id = ?', [$u['id']]);
$pg    = paginate($total, 25, (int)($_GET['page'] ?? 1));
$items = rows('SELECT l.created_at, p.title, p.slug, p.price FROM leads l JOIN products p ON p.id = l.product_id
               WHERE l.seller_id = ? ORDER BY l.id DESC LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'], [$u['id']]);
$per = rows('SELECT p.title, p.slug, COUNT(l.id) AS n FROM leads l JOIN products p ON p.id = l.product_id WHERE l.seller_id = ? GROUP BY p.id ORDER BY n DESC LIMIT 5', [$u['id']]);
$area = 'seller'; $active = 'leads'; $title = 'Prospek WhatsApp';
render('dash/leads', compact('items', 'pg', 'total', 'per', 'area', 'active', 'title'), 'dash');
