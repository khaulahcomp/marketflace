<?php
$u = current_user();
$stats = row("SELECT COUNT(*) AS total,
                     COALESCE(SUM(status = 'published'), 0) AS published,
                     COALESCE(SUM(status = 'pending'), 0) AS pending,
                     COALESCE(SUM(views), 0) AS views,
                     COALESCE(SUM(demo_clicks), 0) AS demos,
                     COALESCE(SUM(wa_clicks), 0) AS wa
              FROM products WHERE seller_id = ?", [$u['id']]);
$leads7 = (int)val('SELECT COUNT(*) FROM leads WHERE seller_id = ? AND created_at > (NOW() - INTERVAL 7 DAY)', [$u['id']]);
$recent = rows('SELECT l.created_at, p.title, p.slug FROM leads l JOIN products p ON p.id = l.product_id WHERE l.seller_id = ? ORDER BY l.id DESC LIMIT 8', [$u['id']]);
$top    = rows("SELECT id, title, slug, views, demo_clicks, wa_clicks FROM products WHERE seller_id = ? AND status = 'published' ORDER BY wa_clicks DESC, views DESC LIMIT 5", [$u['id']]);
$waOk   = valid_wa(normalize_wa((string)$u['whatsapp']));
$area = 'seller'; $active = 'index'; $title = 'Ringkasan';
$top_actions = '<a class="btn btn-primary btn-sm" href="' . e(url('dashboard/produk/tambah')) . '">+ Tambah produk</a>';
render('dash/index', compact('u', 'stats', 'leads7', 'recent', 'top', 'waOk', 'area', 'active', 'title', 'top_actions'), 'dash');
