<?php
$cats = rows("SELECT c.*, (SELECT COUNT(*) FROM products p JOIN users u ON u.id = p.seller_id AND u.status = 'active'
                          WHERE p.category_id = c.id AND p.status = 'published') AS total
              FROM categories c ORDER BY c.sort_order, c.name");
$featured = rows(product_base_sql() . " WHERE p.status = 'published' AND p.is_featured = 1 ORDER BY p.updated_at DESC LIMIT 8");
$latest   = rows(product_base_sql() . " WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT 12");
$stats = [
    'products' => (int)val("SELECT COUNT(*) " . product_from_sql() . " WHERE p.status = 'published'"),
    'sellers'  => (int)val("SELECT COUNT(DISTINCT p.seller_id) " . product_from_sql() . " WHERE p.status = 'published'"),
];
render('home', compact('cats', 'featured', 'latest', 'stats'));
