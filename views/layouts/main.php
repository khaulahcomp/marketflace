<?php
$siteName = setting('site_name', 'Marketplace Digital');
$ttl      = !empty($title) ? $title . ' — ' . $siteName : $siteName . ' — ' . setting('tagline', '');
$desc     = !empty($meta_desc) ? $meta_desc : setting('site_description', '');
$u        = current_user();
$accent   = preg_match('/^#[0-9a-fA-F]{6}$/', (string)setting('accent_color', '')) ? setting('accent_color') : '#1f4fff';
$cats     = all_categories();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($ttl) ?></title>
<meta name="description" content="<?= e(excerpt($desc, 160)) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($ttl) ?>">
<meta property="og:description" content="<?= e(excerpt($desc, 200)) ?>">
<?php $ogImg = !empty($og_image) ? $og_image : (site_logo_file() !== '' ? upload_url(site_logo_file()) : ''); ?>
<?php if ($ogImg !== ''): ?><meta property="og:image" content="<?= e(site_origin() . $ogImg) ?>"><?php endif; ?>
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<?php if (site_logo_file() !== ''): ?><link rel="icon" href="<?= e(upload_url(site_logo_file())) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<style>:root{--accent:<?= e($accent) ?>}</style>
</head>
<body>
<a class="skip" href="#konten">Lewati ke konten</a>
<?php $fsum = flash_summary(); if ($fsum): ?>
<div class="flash-bar" role="region" aria-label="Info flash sale">
  <div class="container flash-bar-in">
    <span class="fb-ttl"><?= bolt_icon(15) ?> FLASH SALE</span>
    <span class="fb-txt"><?= num($fsum['n']) ?> produk diskon, berakhir dalam</span>
    <?= countdown_html($fsum['left'], 'mini', 'Flash sale terdekat berakhir dalam') ?>
    <a class="fb-link" href="<?= e(url('produk?flash=1')) ?>">Lihat promo &rarr;</a>
  </div>
</div>
<?php endif; ?>
<header class="site-header">
  <div class="container bar">
    <a class="brand" href="<?= e(url('')) ?>"><?= brand_inner() ?></a>
    <form class="hsearch" action="<?= e(url('produk')) ?>" method="get" role="search">
      <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Cari software, website, template…" aria-label="Cari produk">
      <button type="submit">Cari</button>
    </form>
    <button class="burger" type="button" aria-label="Buka menu" aria-controls="main-nav" aria-expanded="false" data-toggle="#main-nav">
      <span></span><span></span><span></span>
    </button>
    <nav id="main-nav" class="nav" aria-label="Menu utama">
      <a href="<?= e(url('produk')) ?>">Jelajahi</a>
      <details class="dd">
        <summary>Kategori</summary>
        <div class="dd-menu">
          <?php foreach ($cats as $c): ?>
            <a href="<?= e(url('kategori/' . $c['slug'])) ?>"><span><?= e($c['icon']) ?></span> <?= e($c['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </details>
      <?php if ($u): ?>
        <a href="<?= e(url('dashboard')) ?>">Dashboard</a>
        <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('admin')) ?>">Admin</a><?php endif; ?>
        <form method="post" action="<?= e(url('keluar')) ?>" class="inline"><?= csrf_field() ?><button type="submit" class="linkbtn">Keluar</button></form>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="konten">
<?php foreach (flashes() as $fl): ?>
  <div class="container"><div class="alert alert-<?= e($fl['t']) ?>" role="status"><?= e($fl['m']) ?></div></div>
<?php endforeach; ?>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="container foot">
    <div>
      <a class="brand" href="<?= e(url('')) ?>"><?= brand_inner(22) ?></a>
      <p class="muted"><?= e(setting('tagline', '')) ?></p>
    </div>
    <div>
      <strong>Jelajahi</strong>
      <a href="<?= e(url('produk')) ?>">Semua produk</a>
      <?php foreach (array_slice($cats, 0, 4) as $c): ?>
        <a href="<?= e(url('kategori/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (setting('contact_email', '') !== ''): ?>
    <div>
      <strong>Bantuan</strong>
      <a href="mailto:<?= e(setting('contact_email')) ?>">Hubungi kami</a>
    </div>
    <?php endif; ?>
  </div>
  <div class="container foot-bottom"><?= e(setting('footer_text', '')) ?></div>
</footer>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
