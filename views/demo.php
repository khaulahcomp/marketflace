<?php
$siteName  = setting('site_name', 'Marketplace Digital');
$accent    = preg_match('/^#[0-9a-fA-F]{6}$/', (string)setting('accent_color', '')) ? setting('accent_color') : '#1f4fff';
$storeName = $p['store_name'] !== '' ? $p['store_name'] : $p['seller_name'];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Demo: <?= e($p['title']) ?> — <?= e($siteName) ?></title>
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<style>:root{--accent:<?= e($accent) ?>}</style>
</head>
<body class="demo-body">
<header class="demo-bar">
  <a class="demo-back" href="<?= e(url('produk/' . $p['slug'])) ?>" aria-label="Kembali ke detail produk">‹ <span>Detail produk</span></a>
  <div class="demo-title">
    <strong><?= e($p['title']) ?></strong>
    <small><?= e(demo_host($p)) ?> · <?= e($storeName) ?></small>
  </div>
  <div class="demo-actions">
    <div class="vp" role="group" aria-label="Ukuran tampilan">
      <button type="button" data-vp="100%" class="on" title="Desktop">Desktop</button>
      <button type="button" data-vp="768px" title="Tablet">Tablet</button>
      <button type="button" data-vp="390px" title="Ponsel">Ponsel</button>
    </div>
    <a class="btn btn-ghost btn-sm" href="<?= e($p['demo_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">Buka di tab baru</a>
    <a class="btn btn-wa btn-sm" href="<?= e(url('go/wa/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= wa_icon(16) ?> Beli via WhatsApp</a>
  </div>
</header>
<?php if ($p['demo_note'] !== ''): ?>
  <div class="demo-note-bar"><strong>Info akun demo:</strong> <?= e($p['demo_note']) ?></div>
<?php endif; ?>
<main class="demo-stage">
  <iframe id="demo-frame" src="<?= e($p['demo_url']) ?>" title="Demo <?= e($p['title']) ?>" sandbox="allow-scripts allow-forms allow-same-origin allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer" loading="eager"></iframe>
  <p class="demo-fallback">Demo tidak tampil? Sebagian situs melarang ditampilkan di dalam halaman lain. <a href="<?= e($p['demo_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">Buka demo di tab baru</a>.</p>
</main>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
