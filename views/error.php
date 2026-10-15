<?php
$titles = [403 => 'Akses ditolak', 404 => 'Halaman tidak ditemukan', 419 => 'Sesi kedaluwarsa'];
$title = $titles[$code] ?? 'Terjadi kesalahan';
$noindex = true;
?>
<section class="container narrow center err-page">
  <p class="err-code"><?= (int)$code ?></p>
  <h1><?= e($title) ?></h1>
  <p class="muted"><?= e($msg !== '' ? $msg : 'Alamat yang Anda buka tidak tersedia atau sudah dipindahkan.') ?></p>
  <p><a class="btn btn-primary" href="<?= e(url('')) ?>">Kembali ke beranda</a></p>
</section>
