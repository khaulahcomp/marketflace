<div class="stats">
  <div class="stat"><small>Penjual</small><strong><?= num($s['users']) ?></strong><span><?= $s['pending_u'] ? num($s['pending_u']) . ' menunggu persetujuan' : 'semua aktif' ?></span></div>
  <div class="stat"><small>Produk tayang</small><strong><?= num($s['products']) ?></strong><span><?= num($s['pending_p']) ?> menunggu review</span></div>
  <div class="stat"><small>Kunjungan produk</small><strong><?= num($s['views']) ?></strong><span><?= num($s['demos']) ?> klik demo</span></div>
  <div class="stat stat-wa"><small>Prospek WhatsApp</small><strong><?= num($s['leads']) ?></strong><span><?= num($s['leads7']) ?> dalam 7 hari</span></div>
</div>
<div class="two-col">
  <section class="panel">
    <div class="panel-head"><h2>Menunggu review</h2><a href="<?= e(url('admin/produk?status=pending')) ?>">Semua</a></div>
    <?php if ($pendingProducts): ?>
      <ul class="list">
        <?php foreach ($pendingProducts as $p): ?>
          <li><a href="<?= e(url('dashboard/produk/edit/' . $p['id'])) ?>"><?= e($p['title']) ?></a><small>oleh <?= e($p['store_name'] ?: $p['name']) ?> · <?= e(time_ago($p['created_at'])) ?></small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="muted pad">Tidak ada produk yang menunggu review.</p><?php endif; ?>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Penjual terbaru</h2><a href="<?= e(url('admin/pengguna')) ?>">Semua</a></div>
    <?php if ($newUsers): ?>
      <ul class="list">
        <?php foreach ($newUsers as $n): ?>
          <li><a href="<?= e(url('toko/' . $n['username'])) ?>"><?= e($n['store_name'] ?: $n['name']) ?></a><small><?= e($n['status']) ?> · <?= e(time_ago($n['created_at'])) ?></small></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="muted pad">Belum ada penjual.</p><?php endif; ?>
  </section>
</div>
