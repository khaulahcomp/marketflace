<?php
if (is_post()) {
    csrf_verify();
    $act  = (string)($_POST['act'] ?? '');
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $icon = trim((string)($_POST['icon'] ?? ''));
    $sort = (int)($_POST['sort_order'] ?? 0);
    $len  = function ($s) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); };

    if ($act === 'delete') {
        q('DELETE FROM categories WHERE id = ?', [$id]);
        flash('success', 'Kategori dihapus. Produk di dalamnya menjadi tanpa kategori.');
    } elseif ($act === 'add' || $act === 'update') {
        if ($len($name) < 2 || $len($name) > 80) {
            flash('error', 'Nama kategori 2-80 karakter.');
        } elseif ($len($icon) > 8) {
            flash('error', 'Ikon maksimal 8 karakter (cukup satu emoji).');
        } elseif ($act === 'add') {
            q('INSERT INTO categories (name, slug, icon, sort_order) VALUES (?,?,?,?)', [$name, unique_slug('categories', slugify($name)), $icon, $sort]);
            flash('success', 'Kategori ditambahkan.');
        } else {
            q('UPDATE categories SET name = ?, icon = ?, sort_order = ? WHERE id = ?', [$name, $icon, $sort, $id]);
            flash('success', 'Kategori diperbarui.');
        }
    }
    redirect('admin/kategori');
}
$items = rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS n FROM categories c ORDER BY c.sort_order, c.name');
$area = 'admin'; $active = 'categories'; $title = 'Kategori';
render('admin/categories', compact('items', 'area', 'active', 'title'), 'dash');
