<?php $title = 'Daftar jadi penjual'; $noindex = true; ?>
<section class="container narrow auth">
  <h1 class="h-page">Daftar jadi penjual</h1>
  <p class="muted">Gratis. Nomor WhatsApp Anda dipakai sebagai tujuan tombol “Beli via WhatsApp” di semua produk Anda.</p>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="form card-box" novalidate>
    <?= csrf_field() ?>
    <div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="field">
      <label for="name">Nama lengkap</label>
      <input id="name" name="name" value="<?= e($f['name']) ?>" autocomplete="name" required>
    </div>
    <div class="field">
      <label for="store_name">Nama toko</label>
      <input id="store_name" name="store_name" value="<?= e($f['store_name']) ?>" required>
    </div>
    <div class="row2">
      <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($f['username']) ?>" autocomplete="username" pattern="[a-z0-9_]{3,40}" required>
        <small>Dipakai di alamat toko: /toko/username</small>
      </div>
      <div class="field">
        <label for="whatsapp">Nomor WhatsApp</label>
        <input id="whatsapp" name="whatsapp" value="<?= e($f['whatsapp']) ?>" inputmode="tel" placeholder="081234567890" required>
      </div>
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="<?= e($f['email']) ?>" autocomplete="email" required>
    </div>
    <div class="row2">
      <div class="field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
        <small>Minimal 8 karakter</small>
      </div>
      <div class="field">
        <label for="password2">Ulangi password</label>
        <input id="password2" type="password" name="password2" minlength="8" autocomplete="new-password" required>
      </div>
    </div>
    <label class="check"><input type="checkbox" name="agree" value="1" required> Saya menjual produk milik sendiri atau yang saya berhak jual, dan menyetujui transaksi dilakukan langsung dengan pembeli.</label>
    <button class="btn btn-primary btn-block" type="submit">Buat akun penjual</button>
  </form>
  <p class="center muted">Sudah punya akun? <a href="<?= e(url('masuk')) ?>">Masuk</a></p>
</section>
