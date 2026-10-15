<p class="muted">Setiap kali calon pembeli menekan “Beli via WhatsApp”, tercatat sebagai prospek. Tindak lanjuti lewat WhatsApp Anda. Ini adalah pencatatan klik; isi percakapan tidak terlihat oleh situs ini.</p>
<div class="two-col">
  <section class="panel">
    <div class="panel-head"><h2>Riwayat prospek (<?= num($total) ?>)</h2></div>
    <?php if ($items): ?>
      <ul class="list">
        <?php foreach ($items as $r): ?>
          <li><a href="<?= e(url('produk/' . $r['slug'])) ?>"><?= e($r['title']) ?></a><small><?= e(fdate($r['created_at'], true)) ?></small></li>
        <?php endforeach; ?>
      </ul>
      <div class="pad"><?= pagination_html($pg) ?></div>
    <?php else: ?>
      <p class="muted pad">Belum ada prospek.</p>
    <?php endif; ?>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Produk dengan prospek terbanyak</h2></div>
    <?php if ($per): ?>
      <ul class="list">
        <?php foreach ($per as $r): ?>
          <li><a href="<?= e(url('produk/' . $r['slug'])) ?>"><?= e($r['title']) ?></a><small><?= num($r['n']) ?> prospek</small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted pad">Belum ada data.</p>
    <?php endif; ?>
  </section>
</div>
