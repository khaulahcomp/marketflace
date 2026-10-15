<?php if ($errors): ?>
  <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="two-col top">
  <form method="post" enctype="multipart/form-data" class="form panel pad" novalidate>
    <?= csrf_field() ?><input type="hidden" name="section" value="profile">
    <h2>Profil toko</h2>
    <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" value="<?= e($f['name']) ?>" required></div>
    <div class="field"><label for="store_name">Nama toko</label><input id="store_name" name="store_name" value="<?= e($f['store_name']) ?>" required></div>
    <div class="field">
      <label for="whatsapp">Nomor WhatsApp penjualan</label>
      <input id="whatsapp" name="whatsapp" value="<?= e($f['whatsapp']) ?>" inputmode="tel" required>
      <small>Semua tombol “Beli via WhatsApp” di produk Anda mengarah ke nomor ini.</small>
    </div>
    <div class="field"><label for="store_desc">Tentang toko</label><textarea id="store_desc" name="store_desc" rows="4" maxlength="1000"><?= e($f['store_desc']) ?></textarea></div>
    <div class="field">
      <label for="avatar">Logo toko</label>
      <?php if ($u['avatar']): ?><div class="cur-img"><img class="round" src="<?= e(upload_url($u['avatar'])) ?>" alt="Logo saat ini"></div><?php endif; ?>
      <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
      <small>Maks 1 MB, rasio persegi.</small>
    </div>
    <div class="field"><label>Email</label><input value="<?= e($u['email']) ?>" disabled></div>
    <div class="field"><label>Alamat toko</label><input value="<?= e(abs_url('toko/' . $u['username'])) ?>" readonly onclick="this.select()"></div>
    <button class="btn btn-primary" type="submit">Simpan profil</button>
  </form>

  <form method="post" class="form panel pad" novalidate autocomplete="off">
    <?= csrf_field() ?><input type="hidden" name="section" value="password">
    <h2>Ganti password</h2>
    <div class="field"><label for="cp">Password saat ini</label><input id="cp" type="password" name="current_password" autocomplete="current-password" required></div>
    <div class="field"><label for="np">Password baru</label><input id="np" type="password" name="new_password" minlength="8" autocomplete="new-password" required></div>
    <div class="field"><label for="np2">Ulangi password baru</label><input id="np2" type="password" name="new_password2" minlength="8" autocomplete="new-password" required></div>
    <button class="btn btn-primary" type="submit">Ganti password</button>
  </form>
</div>
