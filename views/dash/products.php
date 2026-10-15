<div class="tabs">
  <a href="<?= e(url('dashboard/produk')) ?>" class="<?= $st === '' ? 'on' : '' ?>">Semua</a>
  <?php foreach (status_labels() as $k => $l): ?>
    <a href="<?= e(url('dashboard/produk?status=' . $k)) ?>" class="<?= $st === $k ? 'on' : '' ?>"><?= e($l) ?> <small><?= num($counts[$k] ?? 0) ?></small></a>
  <?php endforeach; ?>
</div>

<?php if ($items): ?>
<div class="table-wrap">
  <table class="table">
    <thead><tr><th>Produk</th><th>Harga</th><th>Status</th><th class="r">Dilihat</th><th class="r">Demo</th><th class="r">WA</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $p): ?>
      <tr>
        <td>
          <div class="cell-prod">
            <span class="mini"><?= thumb_html($p) ?></span>
            <div><strong><?= e($p['title']) ?></strong><small><?= e(product_types()[$p['type']] ?? '') ?><?= $p['demo_url'] ? ' · ada demo' : '' ?></small></div>
          </div>
        </td>
        <td class="pcell"><?= price_cell_html($p) ?></td>
        <td><?= status_badge($p['status']) ?></td>
        <td class="r"><?= num($p['views']) ?></td>
        <td class="r"><?= num($p['demo_clicks']) ?></td>
        <td class="r"><?= num($p['wa_clicks']) ?></td>
        <td class="acts">
          <a class="btn btn-ghost btn-xs" href="<?= e(url('produk/' . $p['slug'])) ?>" target="_blank" rel="noopener">Lihat</a>
          <a class="btn btn-ghost btn-xs" href="<?= e(url('dashboard/produk/edit/' . $p['id'])) ?>">Edit</a>
          <form method="post" action="<?= e(url('dashboard/produk/hapus')) ?>" class="inline" data-confirm="Hapus produk “<?= e($p['title']) ?>” beserta gambarnya? Tindakan ini tidak bisa dibatalkan.">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-danger-ghost btn-xs" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty">
    <h3>Belum ada produk<?= $st !== '' ? ' dengan status ini' : '' ?></h3>
    <p>Tambahkan produk, isi URL demo, dan calon pembeli bisa langsung menghubungi WhatsApp Anda.</p>
    <a class="btn btn-primary" href="<?= e(url('dashboard/produk/tambah')) ?>">Tambah produk</a>
  </div>
<?php endif; ?>
