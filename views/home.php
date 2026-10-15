<?php $siteName = setting('site_name', 'Marketplace Digital'); ?>
<section class="hero">
  <div class="container hero-grid">
    <div class="hero-copy">
      <h1>Coba demonya dulu.<br>Baru chat penjualnya.</h1>
      <p><?= e(setting('tagline', '')) ?></p>
      <form class="hero-search" action="<?= e(url('produk')) ?>" method="get" role="search">
        <input type="search" name="q" placeholder="Contoh: aplikasi kasir, template toko online…" aria-label="Cari produk">
        <button type="submit" class="btn btn-primary">Cari produk</button>
      </form>
      <p class="hero-stats"><?= num($stats['products']) ?> produk dari <?= num($stats['sellers']) ?> penjual. Tanpa keranjang, tanpa biaya admin: pembayaran langsung ke penjual.</p>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="mock">
        <div class="chrome"><i></i><i></i><i></i><span class="url">demo.tokoanda.id/dashboard</span></div>
        <div class="mock-body">
          <div class="mock-side"><b></b><b></b><b></b><b></b></div>
          <div class="mock-main">
            <div class="mock-row"><span></span><span></span><span></span></div>
            <div class="mock-chart"><s style="height:38%"></s><s style="height:62%"></s><s style="height:48%"></s><s style="height:80%"></s><s style="height:56%"></s><s style="height:92%"></s></div>
            <div class="mock-line"></div><div class="mock-line short"></div>
          </div>
        </div>
      </div>
      <div class="chip chip-demo"><?= ico_demo(15) ?> Demo live aktif</div>
      <div class="chip chip-wa"><?= wa_icon(16) ?><span>Halo, saya tertarik dengan <b>Aplikasi Kasir Pro</b>. Masih tersedia?</span></div>
    </div>
  </div>
</section>

<section class="container steps" aria-label="Cara belanja">
  <ol>
    <li><b>1</b><div><strong>Pilih produk</strong><span>Software, website, template, script, plugin.</span></div></li>
    <li><b>2</b><div><strong>Coba demo langsung</strong><span>Jalankan versi demo sebelum memutuskan.</span></div></li>
    <li><b>3</b><div><strong>Chat penjual via WhatsApp</strong><span>Pesan otomatis berisi produk yang Anda pilih.</span></div></li>
  </ol>
</section>

<section class="container section">
  <div class="sec-head"><h2>Kategori</h2><a href="<?= e(url('produk')) ?>">Lihat semua produk</a></div>
  <div class="cat-grid">
    <?php foreach ($cats as $c): ?>
      <a class="cat" href="<?= e(url('kategori/' . $c['slug'])) ?>">
        <span class="cat-ico"><?= e($c['icon'] !== '' ? $c['icon'] : '📦') ?></span>
        <strong><?= e($c['name']) ?></strong>
        <small><?= num($c['total']) ?> produk</small>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($featured): ?>
<section class="container section">
  <div class="sec-head"><h2>Pilihan editor</h2></div>
  <div class="grid">
    <?php foreach ($featured as $p) include ROOT . '/views/partials/product_card.php'; ?>
  </div>
</section>
<?php endif; ?>

<section class="container section">
  <div class="sec-head"><h2>Produk terbaru</h2><a href="<?= e(url('produk?sort=terbaru')) ?>">Lihat semua</a></div>
  <?php if ($latest): ?>
    <div class="grid">
      <?php foreach ($latest as $p) include ROOT . '/views/partials/product_card.php'; ?>
    </div>
  <?php else: ?>
    <div class="empty">
      <h3>Belum ada produk yang tayang</h3>
      <p>Produk digital akan segera hadir di <?= e($siteName) ?>. Silakan kembali lagi nanti.</p>
    </div>
  <?php endif; ?>
</section>
