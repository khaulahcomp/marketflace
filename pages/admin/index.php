<?php
$s = [
    'users'    => (int)val("SELECT COUNT(*) FROM users WHERE role = 'seller'"),
    'pending_u'=> (int)val("SELECT COUNT(*) FROM users WHERE status = 'pending'"),
    'products' => (int)val("SELECT COUNT(*) FROM products WHERE status = 'published'"),
    'pending_p'=> (int)val("SELECT COUNT(*) FROM products WHERE status = 'pending'"),
    'leads'    => (int)val('SELECT COUNT(*) FROM leads'),
    'leads7'   => (int)val('SELECT COUNT(*) FROM leads WHERE created_at > (NOW() - INTERVAL 7 DAY)'),
    'views'    => (int)val('SELECT COALESCE(SUM(views),0) FROM products'),
    'demos'    => (int)val('SELECT COALESCE(SUM(demo_clicks),0) FROM products'),
];
$pendingProducts = rows("SELECT p.id, p.title, p.created_at, u.store_name, u.name FROM products p JOIN users u ON u.id = p.seller_id WHERE p.status = 'pending' ORDER BY p.id ASC LIMIT 8");
$newUsers = rows("SELECT id, name, username, store_name, status, created_at FROM users WHERE role = 'seller' ORDER BY id DESC LIMIT 6");
$area = 'admin'; $active = 'index'; $title = 'Ringkasan admin';
render('admin/index', compact('s', 'pendingProducts', 'newUsers', 'area', 'active', 'title'), 'dash');
