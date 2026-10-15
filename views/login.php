<?php $title = 'Masuk'; $noindex = true; ?>
<section class="container narrow auth">
  <h1 class="h-page">Masuk</h1>
  <p class="muted">Kelola produk, lihat prospek, dan atur toko Anda.</p>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="form card-box" novalidate>
    <?= csrf_field() ?>
    <div class="field">
      <label for="login">Email atau username</label>
      <input id="login" name="login" value="<?= e($login) ?>" autocomplete="username" required autofocus>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" autocomplete="current-password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Masuk</button>
  </form>
  <?php if (setting('allow_registration', '1') === '1'): ?>
    <p class="center muted">Belum punya akun? <a href="<?= e(url('daftar')) ?>">Daftar jadi penjual</a></p>
  <?php endif; ?>
</section>
