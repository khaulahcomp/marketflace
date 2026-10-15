<?php
$title     = $p['title'];
$meta_desc = $p['short_desc'] !== '' ? $p['short_desc'] : excerpt((string)$p['description'], 160);
$og_image  = $p['thumbnail'] ? upload_url($p['thumbnail']) : '';
$storeName = $p['store_name'] !== '' ? $p['store_name'] : $p['seller_name'];
$shots     = [];
if ($p['thumbnail']) $shots[] = $p['thumbnail'];
foreach ($gallery as $g) $shots[] = $g['filename'];
$features = array_filter(array_map('trim', preg_split('/\R/', (string)$p['features'])));
if ($p['status'] !== 'published') $noindex = true;
$pi      = price_info($p);
$isFlash = $pi['state'] === 'active';
?>
<div class="container section">
  <nav class="crumbs" aria-label="Breadcrumb">
    <a href="<?= e(url('')) ?>">Beranda</a> <span>/</span>
    <?php if ($p['cat_name']): ?><a href="<?= e(url('kategori/' . $p['cat_slug'])) ?>"><?= e($p['cat_name']) ?></a> <span>/</span><?php endif; ?>
    <span aria-current="page"><?= e($p['title']) ?></span>
  </nav>

  <?php if ($p['status'] !== 'published'): ?>
    <div class="alert alert-warn">Pratinjau: produk ini berstatus <strong><?= e(status_labels()[$p['status']] ?? $p['status']) ?></strong> dan belum terlihat publik.</div>
  <?php endif; ?>

  <div class="detail">
    <div class="detail-main">
      <div class="viewer">
        <div class="pframe big">
          <div class="chrome"><i></i><i></i><i></i><span class="url"><?= e(demo_host($p)) ?></span></div>
          <div class="shot" id="main-shot">
            <?php if ($shots): ?>
              <img src="<?= e(upload_url($shots[0])) ?>" alt="<?= e($p['title']) ?>" id="main-img">
            <?php else: ?>
              <?= thumb_html($p) ?>
            <?php endif; ?>
          </div>
        </div>
        <?php if (count($shots) > 1): ?>
          <div class="thumbs">
            <?php foreach ($shots as $i => $s): ?>
              <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-shot="<?= e(upload_url($s)) ?>" aria-label="Gambar <?= $i + 1 ?>"><img src="<?= e(upload_url($s)) ?>" alt="" loading="lazy"></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <h1 class="h-page"><?= e($p['title']) ?></h1>
      <div class="tags">
        <span class="tag"><?= e(product_types()[$p['type']] ?? 'Produk') ?></span>
        <?php if ($p['cat_name']): ?><a class="tag" href="<?= e(url('kategori/' . $p['cat_slug'])) ?>"><?= e($p['cat_name']) ?></a><?php endif; ?>
        <?php if ($p['tech_stack'] !== ''): foreach (array_slice(array_map('trim', explode(',', $p['tech_stack'])), 0, 8) as $t): if ($t === '') continue; ?>
          <span class="tag tag-tech"><?= e($t) ?></span>
        <?php endforeach; endif; ?>
      </div>

      <?php if ($p['short_desc'] !== ''): ?><p class="lede"><?= e($p['short_desc']) ?></p><?php endif; ?>

      <?php if ($features): ?>
        <h2 class="h-sub">Fitur utama</h2>
        <ul class="features">
          <?php foreach ($features as $ft): ?><li><?= e($ft) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <h2 class="h-sub">Deskripsi</h2>
      <div class="prose"><?= nl2br(e((string)$p['description'])) ?></div>
    </div>

    <aside class="buy-panel">
      <div class="buy-box">
        <?php if ($isFlash): ?>
          <div class="flash-box" data-flash-card>
            <div class="flash-head"><span class="flash-badge"><?= bolt_icon(15) ?> FLASH SALE</span><?php if ($pi['pct'] > 0): ?><span class="flash-pct">-<?= (int)$pi['pct'] ?>%</span><?php endif; ?></div>
            <div class="flash-cd-lbl">Berakhir dalam</div>
            <?= countdown_html($pi['left'], 'full') ?>
            <?= price_html($p, 'detail') ?>
            <?php if ($pi['save'] > 0): ?><p class="flash-save">Anda hemat <strong><?= e(rupiah($pi['save'])) ?></strong> selama promo berlangsung.</p><?php endif; ?>
          </div>
        <?php else: ?>
          <?php if ($pi['state'] === 'upcoming'): ?>
            <div class="flash-teaser">
              <strong><?= bolt_icon(14) ?> Flash sale segera dimulai</strong>
              <span>Harga spesial <b><?= e(rupiah($pi['flash_price'])) ?></b> mulai <?= e(fdate($p['flash_start'], true)) ?> WIB.</span>
              <?= countdown_html($pi['left'], 'mini', 'Dimulai dalam') ?>
            </div>
          <?php endif; ?>
          <div class="buy-price"><?= price_html($p, 'detail') ?></div>
        <?php endif; ?>
        <?php if (!empty($p['demo_url'])): ?>
          <a class="btn btn-ghost btn-lg" href="<?= e(url('demo/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= ico_demo(18) ?> Lihat demo</a>
          <?php if ($p['demo_note'] !== ''): ?><p class="demo-note"><strong>Info akun demo:</strong> <?= e($p['demo_note']) ?></p><?php endif; ?>
        <?php endif; ?>
        <a class="btn <?= $isFlash ? 'btn-sale' : 'btn-wa' ?> btn-lg" href="<?= e(url('go/wa/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= wa_icon(20) ?> Beli via WhatsApp<?= $isFlash ? ' sekarang' : '' ?></a>
        <p class="fine">Anda akan diarahkan ke WhatsApp penjual dengan pesan yang sudah terisi. Pembayaran dan pengiriman produk disepakati langsung dengan penjual.</p>
        <dl class="facts">
          <div><dt>Dilihat</dt><dd><?= num($p['views']) ?>×</dd></div>
          <div><dt>Diperbarui</dt><dd><?= e(fdate($p['updated_at'])) ?></dd></div>
        </dl>
      </div>

      <a class="seller-box" href="<?= e(url('toko/' . $p['username'])) ?>">
        <span class="avatar"><?php if ($p['seller_avatar']): ?><img src="<?= e(upload_url($p['seller_avatar'])) ?>" alt=""><?php else: ?><?= e(initial($storeName)) ?><?php endif; ?></span>
        <span><strong><?= e($storeName) ?></strong><small><?= num($sellerCount) ?> produk tayang · Lihat toko</small></span>
      </a>
    </aside>
  </div>

<div class="mobile-bar<?= $pi['current'] > 0 ? ' has-price' : '' ?><?= $isFlash ? ' has-sale' : '' ?>">
  <?php if ($pi['current'] > 0): ?>
    <div class="mb-price<?= $isFlash ? ' is-flash' : '' ?>">
      <div>
        <?php if ($pi['strike'] > 0): ?><del class="muted"><?= e(rupiah($pi['strike'])) ?></del><?php endif; ?>
        <strong><?= e(rupiah($pi['current'])) ?></strong>
        <?php if ($pi['pct'] > 0): ?><span class="save-pill">-<?= (int)$pi['pct'] ?>%</span><?php endif; ?>
      </div>
      <?php if ($isFlash): ?><div class="mb-cd"><?= bolt_icon(13) ?> <?= countdown_html($pi['left'], 'mini') ?></div><?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($p['demo_url'])): ?>
    <a class="btn btn-ghost" href="<?= e(url('demo/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= ico_demo(16) ?> Demo</a>
  <?php endif; ?>
  <a class="btn <?= $isFlash ? 'btn-sale' : 'btn-wa' ?> grow" href="<?= e(url('go/wa/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= wa_icon(18) ?> Beli via WhatsApp</a>
</div>

  <?php if ($related): ?>
    <div class="section">
      <div class="sec-head"><h2>Produk serupa</h2></div>
      <div class="grid">
        <?php foreach ($related as $p) include ROOT . '/views/partials/product_card.php'; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

