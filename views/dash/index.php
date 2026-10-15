<?php if (!$waOk): ?>
  <div class="alert alert-warn">Nomor WhatsApp Anda belum valid, sehingga tombol “Beli via WhatsApp” tidak berfungsi. <a href="<?= e(url('dashboard/profil')) ?>">Perbaiki di profil</a>.</div>
<?php endif; ?>

<div class="stats">
  <div class="stat"><small>Produk tayang</small><strong><?= num($stats['published']) ?></strong><span><?= num($stats['total']) ?> total<?= $stats['pending'] ? ' · ' . num($stats['pending']) . ' menunggu review' : '' ?></span></div>
  <div class="stat"><small>Dilihat</small><strong><?= num($stats['views']) ?></strong><span>kunjungan halaman produk</span></div>
  <div class="stat"><small>Klik demo</small><strong><?= num($stats['demos']) ?></strong><span>calon pembeli mencoba demo</span></div>
  <div class="stat stat-wa"><small>Klik WhatsApp</small><strong><?= num($stats['wa']) ?></strong><span><?= num($leads7) ?> prospek dalam 7 hari</span></div>
</div>

<div class="two-col">
  <section class="panel">
    <div class="panel-head"><h2>Prospek terbaru</h2><a href="<?= e(url('dashboard/prospek')) ?>">Semua</a></div>
    <?php if ($recent): ?>
      <ul class="list">
        <?php foreach ($recent as $r): ?>
          <li><a href="<?= e(url('produk/' . $r['slug'])) ?>"><?= e($r['title']) ?></a><small><?= e(time_ago($r['created_at'])) ?></small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted pad">Belum ada calon pembeli yang menekan tombol WhatsApp.</p>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Produk paling diminati</h2><a href="<?= e(url('dashboard/produk')) ?>">Kelola</a></div>
    <?php if ($top): ?>
      <ul class="list">
        <?php foreach ($top as $t): ?>
          <li><a href="<?= e(url('dashboard/produk/edit/' . $t['id'])) ?>"><?= e($t['title']) ?></a><small><?= num($t['views']) ?> dilihat · <?= num($t['demo_clicks']) ?> demo · <?= num($t['wa_clicks']) ?> WA</small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted pad">Belum ada produk tayang. <a href="<?= e(url('dashboard/produk/tambah')) ?>">Tambah produk pertama</a>.</p>
    <?php endif; ?>
  </section>
</div>
