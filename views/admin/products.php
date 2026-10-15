<div class="tabs">
  <a href="<?= e(url('admin/produk')) ?>" class="<?= $st === '' ? 'on' : '' ?>">Semua</a>
  <?php foreach (status_labels() as $k => $l): ?>
    <a href="<?= e(url('admin/produk?status=' . $k)) ?>" class="<?= $st === $k ? 'on' : '' ?>"><?= e($l) ?> <small><?= num($counts[$k] ?? 0) ?></small></a>
  <?php endforeach; ?>
</div>
<form class="toolbar" method="get">
  <?php if ($st !== ''): ?><input type="hidden" name="status" value="<?= e($st) ?>"><?php endif; ?>
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul atau toko…" aria-label="Cari produk">
  <button class="btn btn-primary btn-sm" type="submit">Cari</button>
</form>
<p class="muted"><?= num($total) ?> produk</p>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Produk</th><th>Penjual</th><th>Harga</th><th>Status</th><th class="r">Dilihat</th><th class="r">WA</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $p): ?>
    <tr>
      <td>
        <div class="cell-prod"><span class="mini"><?= thumb_html($p) ?></span>
          <div><strong><?= e($p['title']) ?><?= $p['is_featured'] ? ' ★' : '' ?></strong><small><?= e(product_types()[$p['type']] ?? '') ?><?= $p['demo_url'] ? ' · <a href="' . e($p['demo_url']) . '" target="_blank" rel="noopener noreferrer nofollow">demo</a>' : '' ?></small></div>
        </div>
      </td>
      <td><?= e($p['store_name'] ?: $p['seller_name']) ?><?= $p['seller_status'] !== 'active' ? '<small>akun ' . e($p['seller_status']) . '</small>' : '' ?></td>
      <td><?= e(rupiah($p['price'])) ?></td>
      <td><?= status_badge($p['status']) ?></td>
      <td class="r"><?= num($p['views']) ?></td>
      <td class="r"><?= num($p['wa_clicks']) ?></td>
      <td class="acts">
        <a class="btn btn-ghost btn-xs" href="<?= e(url('produk/' . $p['slug'])) ?>" target="_blank" rel="noopener">Lihat</a>
        <a class="btn btn-ghost btn-xs" href="<?= e(url('dashboard/produk/edit/' . $p['id'])) ?>">Edit</a>
        <?php
        $acts = [];
        if ($p['status'] !== 'published') $acts[] = ['approve', 'Setujui', 'btn-primary', ''];
        if ($p['status'] === 'pending' || $p['status'] === 'published') $acts[] = ['reject', 'Tolak', 'btn-ghost', ''];
        if ($p['status'] === 'published') $acts[] = $p['is_featured'] ? ['unfeature', 'Lepas ★', 'btn-ghost', ''] : ['feature', '★ Pilihan', 'btn-ghost', ''];
        $acts[] = ['delete', 'Hapus', 'btn-danger-ghost', 'Hapus produk ini beserta gambarnya?'];
        foreach ($acts as $a): ?>
          <form method="post" class="inline" <?= $a[3] !== '' ? 'data-confirm="' . e($a[3]) . '"' : '' ?>>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="act" value="<?= e($a[0]) ?>"><input type="hidden" name="qs" value="<?= e($qs) ?>">
            <button class="btn <?= e($a[2]) ?> btn-xs" type="submit"><?= e($a[1]) ?></button>
          </form>
        <?php endforeach; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="7" class="muted center pad">Tidak ada produk.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?= pagination_html($pg) ?>
