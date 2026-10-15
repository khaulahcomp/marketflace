<?php if ($errors): ?>
  <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="two-col top">
  <form method="post" class="form panel pad" novalidate autocomplete="off">
    <?= csrf_field() ?><input type="hidden" name="section" value="username">
    <h2>Ganti username</h2>
    <p class="muted">Username saat ini: <strong><?= e($u['username']) ?></strong>. Anda tetap bisa masuk memakai email.</p>
    <div class="field">
      <label for="username">Username baru</label>
      <input id="username" name="username" value="<?= e($username) ?>" maxlength="40" autocapitalize="none" spellcheck="false" required>
      <small>3-40 karakter: huruf kecil, angka, atau underscore.</small>
    </div>
    <div class="field"><label for="cp1">Password saat ini</label><input id="cp1" type="password" name="current_password" autocomplete="current-password" required></div>
    <button class="btn btn-primary" type="submit">Simpan username</button>
  </form>

  <form method="post" class="form panel pad" novalidate autocomplete="off">
    <?= csrf_field() ?><input type="hidden" name="section" value="password">
    <h2>Ganti password</h2>
    <div class="field"><label for="cp2">Password saat ini</label><input id="cp2" type="password" name="current_password" autocomplete="current-password" required></div>
    <div class="field"><label for="np">Password baru</label><input id="np" type="password" name="new_password" minlength="8" maxlength="72" autocomplete="new-password" required><small>Minimal 8 karakter.</small></div>
    <div class="field"><label for="np2">Ulangi password baru</label><input id="np2" type="password" name="new_password2" minlength="8" maxlength="72" autocomplete="new-password" required></div>
    <button class="btn btn-primary" type="submit">Ganti password</button>
  </form>
</div>
