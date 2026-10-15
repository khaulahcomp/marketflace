<?php
$siteName = setting('site_name', 'Marketplace Digital');
$u        = current_user();
$accent   = preg_match('/^#[0-9a-fA-F]{6}$/', (string)setting('accent_color', '')) ? setting('accent_color') : '#1f4fff';
$area     = $area ?? 'seller';
$active   = $active ?? '';
if ($area === 'admin') {
    $nav = [
        'index'      => ['admin', 'Ringkasan'],
        'products'   => ['admin/produk', 'Produk'],
        'users'      => ['admin/pengguna', 'Pengguna'],
        'categories' => ['admin/kategori', 'Kategori'],
        'settings'   => ['admin/pengaturan', 'Pengaturan'],
        'account'    => ['admin/akun', 'Akun admin'],
    ];
} else {
    $nav = [
        'index'    => ['dashboard', 'Ringkasan'],
        'products' => ['dashboard/produk', 'Produk saya'],
        'leads'    => ['dashboard/prospek', 'Prospek WhatsApp'],
        'profile'  => ['dashboard/profil', 'Profil & toko'],
    ];
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e(($title ?? 'Dashboard') . ' — ' . $siteName) ?></title>
<?php if (site_logo_file() !== ''): ?><link rel="icon" href="<?= e(upload_url(site_logo_file())) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<style>:root{--accent:<?= e($accent) ?>}</style>
</head>
<body class="dash-body">
<div class="dash">
  <aside class="dash-side" id="dash-side">
    <a class="brand" href="<?= e(url('')) ?>"><?= brand_inner(24) ?></a>
    <div class="area-switch">
      <a href="<?= e(url('dashboard')) ?>" class="<?= $area === 'seller' ? 'on' : '' ?>">Penjual</a>
      <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('admin')) ?>" class="<?= $area === 'admin' ? 'on' : '' ?>">Admin</a><?php endif; ?>
    </div>
    <nav>
      <?php foreach ($nav as $key => $n): ?>
        <a href="<?= e(url($n[0])) ?>" class="<?= $active === $key ? 'on' : '' ?>"><?= e($n[1]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <div class="who"><strong><?= e($u['name']) ?></strong><small><?= e($u['email']) ?></small></div>
      <a href="<?= e(url('')) ?>">Lihat situs</a>
      <form method="post" action="<?= e(url('keluar')) ?>"><?= csrf_field() ?><button type="submit" class="linkbtn">Keluar</button></form>
    </div>
  </aside>
  <div class="dash-main">
    <div class="dash-top">
      <button class="burger" type="button" aria-label="Menu" aria-expanded="false" data-toggle="#dash-side"><span></span><span></span><span></span></button>
      <h1><?= e($title ?? 'Dashboard') ?></h1>
      <div class="dash-actions"><?= $top_actions ?? '' ?></div>
    </div>
    <?php foreach (flashes() as $fl): ?>
      <div class="alert alert-<?= e($fl['t']) ?>" role="status"><?= e($fl['m']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
  </div>
</div>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
