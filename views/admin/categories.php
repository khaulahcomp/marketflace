<div class="two-col top">
  <section class="panel">
    <div class="panel-head"><h2>Daftar kategori</h2></div>
    <div class="cat-rows">
    <?php foreach ($items as $c): ?>
      <form method="post" class="cat-row">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <input name="icon" value="<?= e($c['icon']) ?>" class="ic" maxlength="8" aria-label="Ikon">
        <input name="name" value="<?= e($c['name']) ?>" aria-label="Nama kategori" required>
        <input name="sort_order" type="number" value="<?= (int)$c['sort_order'] ?>" class="so" aria-label="Urutan">
        <small class="cnt"><?= num($c['n']) ?> produk</small>
        <button class="btn btn-ghost btn-xs" name="act" value="update" type="submit">Simpan</button>
        <button class="btn btn-danger-ghost btn-xs" name="act" value="delete" type="submit" data-confirm-btn="Hapus kategori “<?= e($c['name']) ?>”? Produk di dalamnya menjadi tanpa kategori.">Hapus</button>
      </form>
    <?php endforeach; ?>
    </div>
  </section>
  <form method="post" class="form panel pad">
    <?= csrf_field() ?><input type="hidden" name="act" value="add">
    <h2>Tambah kategori</h2>
    <div class="field"><label for="n">Nama</label><input id="n" name="name" required maxlength="80"></div>
    <div class="row2">
      <div class="field"><label for="i">Ikon (emoji)</label><input id="i" name="icon" maxlength="8" placeholder="📦"></div>
      <div class="field"><label for="s">Urutan</label><input id="s" name="sort_order" type="number" value="<?= count($items) + 1 ?>"></div>
    </div>
    <button class="btn btn-primary" type="submit">Tambah</button>
  </form>
</div>
