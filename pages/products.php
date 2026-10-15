<?php
$q    = trim((string)($_GET['q'] ?? ''));
$kat  = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($_GET['kategori'] ?? '')));
$tipe = (string)($_GET['tipe'] ?? '');
$sort = (string)($_GET['sort'] ?? 'terbaru');
$min  = (int)preg_replace('/\D/', '', (string)($_GET['min'] ?? ''));
$max  = (int)preg_replace('/\D/', '', (string)($_GET['max'] ?? ''));
$demo = !empty($_GET['demo']);

$where  = ["p.status = 'published'"];
$params = [];

if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(p.title LIKE ? OR p.short_desc LIKE ? OR p.tech_stack LIKE ? OR u.store_name LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}
$catRow = null;
if ($kat !== '') {
    $catRow = row('SELECT * FROM categories WHERE slug = ?', [$kat]);
    if (!$catRow) abort(404, 'Kategori tidak ditemukan.');
    $where[] = 'p.category_id = ?';
    $params[] = $catRow['id'];
}
if ($tipe !== '' && isset(product_types()[$tipe])) {
    $where[] = 'p.type = ?';
    $params[] = $tipe;
} else {
    $tipe = '';
}
$eff = eff_price_sql();   // harga yang berlaku saat ini (memperhitungkan flash sale aktif)
if ($min > 0) { $where[] = $eff . ' >= ?'; $params[] = $min; }
if ($max > 0) { $where[] = $eff . ' <= ?'; $params[] = $max; }
if ($demo)    { $where[] = "p.demo_url <> ''"; }
$flashOnly = !empty($_GET['flash']) && flash_ready();
if ($flashOnly) { $where[] = flash_active_sql(); }

$orders = [
    'terbaru'  => 'p.created_at DESC',
    'populer'  => 'p.views DESC, p.created_at DESC',
    'termurah' => $eff . ' ASC, p.created_at DESC',
    'termahal' => $eff . ' DESC, p.created_at DESC',
];
if ($flashOnly) {
    $orders['berakhir'] = 'p.flash_end ASC, p.created_at DESC';   // yang paling cepat berakhir tampil dulu
    if (!isset($_GET['sort'])) $sort = 'berakhir';
}
if (!isset($orders[$sort])) $sort = 'terbaru';

$w     = implode(' AND ', $where);
$total = (int)val('SELECT COUNT(*) ' . product_from_sql() . ' WHERE ' . $w, $params);
$pg    = paginate($total, 12, (int)($_GET['page'] ?? 1));
$items = rows(product_base_sql() . ' WHERE ' . $w . ' ORDER BY ' . $orders[$sort] . ' LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'], $params);

render('products', compact('items', 'pg', 'q', 'kat', 'catRow', 'tipe', 'sort', 'min', 'max', 'demo', 'total', 'flashOnly'));
