<?php if (!$waOk): ?>
  <div class="alert alert-warn">Nomor WhatsApp akun Anda belum valid. Tombol beli tidak akan berfungsi sebelum Anda <a href="<?= e(url('dashboard/profil')) ?>">memperbaikinya di profil</a>.</div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-error"><strong>Periksa kembali isian Anda:</strong><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form pform" novalidate>
  <?= csrf_field() ?>

  <section class="panel pad">
    <h2>Informasi produk</h2>
    <div class="field">
      <label for="title">Nama produk</label>
      <input id="title" name="title" value="<?= e($f['title']) ?>" maxlength="150" required>
    </div>
    <div class="row2">
      <div class="field">
        <label for="type">Jenis produk</label>
        <select id="type" name="type">
          <?php foreach (product_types() as $k => $l): ?><option value="<?= e($k) ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id">
          <option value="0">— Tanpa kategori —</option>
          <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$f['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['icon'] . ' ' . $c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field">
      <label for="short_desc">Ringkasan singkat</label>
      <input id="short_desc" name="short_desc" value="<?= e($f['short_desc']) ?>" maxlength="255">
      <small>Satu kalimat yang tampil di kartu produk dan hasil pencarian.</small>
    </div>
    <div class="field">
      <label for="description">Deskripsi lengkap</label>
      <textarea id="description" name="description" rows="8"><?= e($f['description']) ?></textarea>
      <small>Jelaskan apa yang didapat pembeli: isi paket, lisensi, syarat server, dukungan purna jual.</small>
    </div>
    <div class="field">
      <label for="features">Fitur utama</label>
      <textarea id="features" name="features" rows="5" placeholder="Satu fitur per baris"><?= e($f['features']) ?></textarea>
    </div>
    <div class="field">
      <label for="tech_stack">Teknologi</label>
      <input id="tech_stack" name="tech_stack" value="<?= e($f['tech_stack']) ?>" placeholder="PHP, MySQL, Laravel, Bootstrap" maxlength="255">
      <small>Pisahkan dengan koma.</small>
    </div>
  </section>

  <section class="panel pad">
    <h2>Harga & demo</h2>
    <div class="row2">
      <div class="field">
        <label for="price">Harga (Rp)</label>
        <input id="price" name="price" value="<?= $f['price'] ? e(number_format((float)$f['price'], 0, ',', '.')) : '' ?>" inputmode="numeric" data-money placeholder="0">
        <small>Kosongkan atau isi 0 untuk menampilkan “Hubungi penjual”.</small>
      </div>
      <div class="field">
        <label for="demo_url">URL demo</label>
        <input id="demo_url" type="url" name="demo_url" value="<?= e($f['demo_url']) ?>" placeholder="https://demo.contoh.com" maxlength="255">
        <small>Jika diisi, muncul tombol “Lihat demo” di produk Anda.</small>
      </div>
    </div>
    <div class="field">
      <label for="demo_note">Info akun demo (opsional)</label>
      <input id="demo_note" name="demo_note" value="<?= e($f['demo_note']) ?>" placeholder="Login: admin@demo.com / Password: demo123" maxlength="500">
      <small>Tampil di samping tombol demo. Jangan isi dengan akun yang sebenarnya.</small>
    </div>
  </section>

  <section class="panel pad">
    <h2>Gambar</h2>
    <div class="field">
      <label for="thumbnail">Gambar utama</label>
      <?php if ($product && $product['thumbnail']): ?>
        <div class="cur-img"><img src="<?= e(upload_url($product['thumbnail'])) ?>" alt="Gambar utama saat ini"><small>Unggah baru untuk mengganti.</small></div>
      <?php endif; ?>
      <input id="thumbnail" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" data-preview="#prev-thumb">
      <img id="prev-thumb" class="prev" alt="" hidden>
      <small>JPG/PNG/WEBP, maks 2 MB. Disarankan rasio 16:10 (mis. 1280×800), berupa tangkapan layar produk.</small>
    </div>
    <div class="field">
      <label for="gallery">Galeri tambahan (maks 4)</label>
      <?php if ($gallery): ?>
        <div class="gal-edit">
          <?php foreach ($gallery as $g): ?>
            <label class="gal-item"><img src="<?= e(upload_url($g['filename'])) ?>" alt=""><span><input type="checkbox" name="del_img[]" value="<?= (int)$g['id'] ?>"> Hapus</span></label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <input id="gallery" type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>
    </div>
  </section>

  <div class="form-actions">
    <a class="btn btn-ghost" href="<?= e(url('dashboard/produk')) ?>">Batal</a>
    <button class="btn btn-ghost" type="submit" name="submit_action" value="draft">Simpan sebagai draft</button>
    <button class="btn btn-primary" type="submit" name="submit_action" value="publish"><?= setting('moderate_products', '1') === '1' && !is_admin() ? 'Kirim untuk review' : 'Terbitkan' ?></button>
  </div>
</form>
