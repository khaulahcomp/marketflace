<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form pform" novalidate>
  <?= csrf_field() ?>
  <section class="panel pad">
    <h2>Identitas situs</h2>
    <div class="row2">
      <div class="field"><label for="site_name">Nama situs</label><input id="site_name" name="site_name" value="<?= e($f['site_name']) ?>" maxlength="60" required></div>
      <div class="field"><label for="accent_color">Warna utama</label><input id="accent_color" type="color" name="accent_color" value="<?= e($f['accent_color'] ?: '#1f4fff') ?>" class="color"></div>
    </div>
    <div class="field"><label for="tagline">Tagline</label><input id="tagline" name="tagline" value="<?= e($f['tagline']) ?>"></div>
    <div class="field"><label for="site_description">Deskripsi situs (SEO)</label><textarea id="site_description" name="site_description" rows="3"><?= e($f['site_description']) ?></textarea></div>
    <div class="row2">
      <div class="field"><label for="contact_email">Email kontak</label><input id="contact_email" type="email" name="contact_email" value="<?= e($f['contact_email']) ?>"></div>
      <div class="field"><label for="footer_text">Teks footer</label><input id="footer_text" name="footer_text" value="<?= e($f['footer_text']) ?>"></div>
    </div>
  </section>

  <section class="panel pad">
    <h2>Logo situs</h2>
    <?php if ($logo): ?>
      <div class="logo-prev"><img src="<?= e(upload_url($logo)) ?>" alt="Logo saat ini"></div>
    <?php endif; ?>
    <div class="field">
      <label for="site_logo"><?= $logo ? 'Ganti logo' : 'Unggah logo' ?></label>
      <input id="site_logo" type="file" name="site_logo" accept="image/jpeg,image/png,image/webp">
      <small>Format JPG, PNG, atau WEBP, maks 2 MB. Disarankan PNG berlatar transparan dan berbentuk memanjang (contoh 400×100 px). Logo juga dipakai sebagai ikon tab browser dan gambar saat tautan dibagikan.</small>
    </div>
    <label class="check"><input type="checkbox" name="logo_show_name" value="1" <?= $f['logo_show_name'] === '1' ? 'checked' : '' ?>> Tampilkan nama situs di samping logo</label>
    <?php if ($logo): ?>
      <label class="check"><input type="checkbox" name="remove_logo" value="1"> Hapus logo (kembali ke ikon bawaan)</label>
    <?php endif; ?>
  </section>

  <section class="panel pad">
    <h2>Pesan WhatsApp otomatis</h2>
    <div class="field">
      <label for="wa_message">Template pesan</label>
      <textarea id="wa_message" name="wa_message" rows="6"><?= e($f['wa_message']) ?></textarea>
      <small>Terisi otomatis saat pembeli menekan “Beli via WhatsApp”. Variabel: <code>{toko}</code> <code>{produk}</code> <code>{harga}</code> <code>{situs}</code> <code>{link}</code>. Gunakan *teks* untuk huruf tebal di WhatsApp.</small>
    </div>
  </section>

  <section class="panel pad">
    <h2>Pendaftaran & moderasi</h2>
    <label class="check"><input type="checkbox" name="allow_registration" value="1" <?= $f['allow_registration'] === '1' ? 'checked' : '' ?>> Izinkan pendaftaran penjual baru</label>
    <label class="check"><input type="checkbox" name="seller_approval" value="1" <?= $f['seller_approval'] === '1' ? 'checked' : '' ?>> Akun penjual baru harus disetujui admin dulu</label>
    <label class="check"><input type="checkbox" name="moderate_products" value="1" <?= $f['moderate_products'] === '1' ? 'checked' : '' ?>> Produk baru harus direview admin sebelum tayang</label>
  </section>

  <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan pengaturan</button></div>
</form>
