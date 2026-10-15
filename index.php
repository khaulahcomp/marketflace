<?php
/**
 * Front controller: semua request (non-file) masuk lewat sini.
 */
// Syarat minimum: PHP 7.4 ke atas. Tidak ada batas versi maksimum (7.4, 8.0, 8.1, 8.2, 8.3, dst.).
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    http_response_code(500);
    exit('Aplikasi ini membutuhkan PHP 7.4 atau lebih baru (terdeteksi ' . PHP_VERSION . '). Ubah versi PHP lewat MultiPHP Manager di cPanel.');
}

require __DIR__ . '/app/bootstrap.php';

$uri  = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$base = base_path();
$path = trim(substr($uri, strlen($base)), '/');
if ($path === 'index.php') {
    $path = '';
}
$seg = $path === '' ? [] : explode('/', $path);
$r   = $seg[0] ?? '';

$isSlug = function ($s) {
    return is_string($s) && (bool)preg_match('/^[a-z0-9][a-z0-9\-]{0,189}$/', $s);
};

switch ($r) {
    case '':
        require ROOT . '/pages/home.php';
        break;

    case 'produk':
        if (isset($seg[1])) {
            if (!$isSlug($seg[1])) abort(404);
            $slug = $seg[1];
            require ROOT . '/pages/product.php';
        } else {
            require ROOT . '/pages/products.php';
        }
        break;

    case 'kategori':
        if (!isset($seg[1]) || !$isSlug($seg[1])) abort(404);
        $_GET['kategori'] = $seg[1];
        require ROOT . '/pages/products.php';
        break;

    case 'demo':
        if (!isset($seg[1]) || !$isSlug($seg[1])) abort(404);
        $slug = $seg[1];
        require ROOT . '/pages/demo.php';
        break;

    case 'go':
        if (($seg[1] ?? '') !== 'wa' || !$isSlug($seg[2] ?? '')) abort(404);
        $slug = $seg[2];
        require ROOT . '/pages/go_wa.php';
        break;

    case 'toko':
        if (!isset($seg[1]) || !preg_match('/^[a-z0-9_]{3,40}$/', $seg[1])) abort(404);
        $username = $seg[1];
        require ROOT . '/pages/store.php';
        break;

    case 'masuk':
        require ROOT . '/pages/login.php';
        break;
    case 'daftar':
        require ROOT . '/pages/register.php';
        break;
    case 'keluar':
        require ROOT . '/pages/logout.php';
        break;

    case 'dashboard':
        require_login();
        $s1 = $seg[1] ?? '';
        if ($s1 === '') {
            require ROOT . '/pages/dash/index.php';
        } elseif ($s1 === 'produk') {
            $action = $seg[2] ?? '';
            if ($action === '' || $action === 'hapus') {
                require ROOT . '/pages/dash/products.php';
            } elseif ($action === 'tambah' || $action === 'edit') {
                require ROOT . '/pages/dash/product_form.php';
            } else {
                abort(404);
            }
        } elseif ($s1 === 'prospek') {
            require ROOT . '/pages/dash/leads.php';
        } elseif ($s1 === 'profil') {
            require ROOT . '/pages/dash/profile.php';
        } else {
            abort(404);
        }
        break;

    case 'admin':
        require_admin();
        $s1 = $seg[1] ?? '';
        $map = [
            ''           => 'index',
            'pengguna'   => 'users',
            'produk'     => 'products',
            'kategori'   => 'categories',
            'pengaturan' => 'settings',
            'akun'       => 'account',
        ];
        if (!isset($map[$s1])) abort(404);
        require ROOT . '/pages/admin/' . $map[$s1] . '.php';
        break;

    case 'sitemap.xml':
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo '<url><loc>' . e(abs_url('')) . '</loc></url>' . "\n";
        echo '<url><loc>' . e(abs_url('produk')) . '</loc></url>' . "\n";
        foreach (all_categories() as $c) {
            echo '<url><loc>' . e(abs_url('kategori/' . $c['slug'])) . '</loc></url>' . "\n";
        }
        foreach (rows("SELECT p.slug, p.updated_at " . product_from_sql() . " WHERE p.status = 'published' ORDER BY p.id DESC LIMIT 5000") as $p) {
            echo '<url><loc>' . e(abs_url('produk/' . $p['slug'])) . '</loc><lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod></url>' . "\n";
        }
        echo '</urlset>';
        break;

    case 'robots.txt':
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nDisallow: " . url('go/') . "\nDisallow: " . url('dashboard') . "\nDisallow: " . url('admin') . "\nDisallow: " . url('masuk') . "\nDisallow: " . url('daftar') . "\nDisallow: " . url('demo/') . "\n\nSitemap: " . abs_url('sitemap.xml') . "\n";
        break;

    default:
        abort(404);
}
