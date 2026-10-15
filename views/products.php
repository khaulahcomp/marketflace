<?php
$title = $catRow ? $catRow['name'] : ($q !== '' ? 'Hasil pencarian: ' . $q : 'Semua produk');
$noindex = ($q !== '' || $pg['page'] > 1);
?>
<div class="container section">
  <div class="sec-head">
    <div>
      <h1 class="h-page"><?= $catRow ? e($catRow['icon'] . ' ' . $catRow['name']) : ($q !== '' ? 'Hasil untuk “' . e($q) . '”' : 'Semua produk') ?></h1>
      <p class="muted"><?= num($total) ?> produk ditemukan</p>
    </div>
  </div>

  <div class="listing">
    <form class="filters" method="get" action="<?= e($catRow ? url('kategori/' . $catRow['slug']) : url('produk')) ?>">
      <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
      <div class="field">
        <label for="f-sort">Urutkan</label>
        <select id="f-sort" name="sort">
          <?php foreach (['terbaru' => 'Terbaru', 'populer' => 'Terpopuler', 'termurah' => 'Harga terendah', 'termahal' => 'Harga tertinggi'] as $k => $l): ?>
            <option value="<?= e($k) ?>" <?= $sort === $k ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (!$catRow): ?>
      <div class="field">
        <label for="f-kat">Kategori</label>
        <select id="f-kat" onchange="if(this.value){location.href=this.value}">
          <option value="<?= e(url('produk')) ?>">Semua kategori</option>
          <?php foreach (all_categories() as $c): ?>
            <option value="<?= e(url('kategori/' . $c['slug'])) ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="field">
        <label for="f-tipe">Jenis produk</label>
        <select id="f-tipe" name="tipe">
          <option value="">Semua jenis</option>
          <?php foreach (product_types() as $k => $l): ?>
            <option value="<?= e($k) ?>" <?= $tipe === $k ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Rentang harga (Rp)</label>
        <div class="pair">
          <input type="text" inputmode="numeric" name="min" value="<?= $min ? e($min) : '' ?>" placeholder="Min" data-money aria-label="Harga minimum">
          <input type="text" inputmode="numeric" name="max" value="<?= $max ? e($max) : '' ?>" placeholder="Maks" data-money aria-label="Harga maksimum">
        </div>
      </div>
      <label class="check"><input type="checkbox" name="demo" value="1" <?= $demo ? 'checked' : '' ?>> Hanya yang ada demo</label>
      <button class="btn btn-primary" type="submit">Terapkan</button>
      <a class="btn btn-ghost" href="<?= e($catRow ? url('kategori/' . $catRow['slug']) : url('produk')) ?>">Reset</a>
    </form>

    <div>
      <?php if ($items): ?>
        <div class="grid grid-3">
          <?php foreach ($items as $p) include ROOT . '/views/partials/product_card.php'; ?>
        </div>
        <?= pagination_html($pg) ?>
      <?php else: ?>
        <div class="empty">
          <h3>Tidak ada produk yang cocok</h3>
          <p>Coba kata kunci lain atau longgarkan filter harga dan jenis produk.</p>
          <a class="btn btn-primary" href="<?= e(url('produk')) ?>">Lihat semua produk</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
