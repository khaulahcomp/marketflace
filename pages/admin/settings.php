<?php
$keys = ['site_name', 'tagline', 'site_description', 'contact_email', 'footer_text', 'accent_color', 'wa_message'];
$errors = [];
$f = [];
foreach ($keys as $k) $f[$k] = (string)setting($k, '');
foreach (['allow_registration' => '1', 'seller_approval' => '0', 'moderate_products' => '1', 'logo_show_name' => '1'] as $k => $d) $f[$k] = (string)setting($k, $d);
$logo = site_logo_file();

if (is_post()) {
    csrf_verify();
    foreach ($keys as $k) $f[$k] = trim((string)($_POST[$k] ?? ''));
    foreach (['allow_registration', 'seller_approval', 'moderate_products', 'logo_show_name'] as $k) $f[$k] = !empty($_POST[$k]) ? '1' : '0';

    if ($f['site_name'] === '' || strlen($f['site_name']) > 60) $errors[] = 'Nama situs wajib diisi (maks 60 karakter).';
    if ($f['contact_email'] !== '' && !filter_var($f['contact_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email kontak tidak valid.';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $f['accent_color'])) $errors[] = 'Warna aksen harus berformat hex, contoh #1f4fff.';
    if ($f['wa_message'] === '' || strpos($f['wa_message'], '{produk}') === false) $errors[] = 'Template pesan WhatsApp wajib memuat {produk}.';
    if (strlen($f['wa_message']) > 1000) $errors[] = 'Template pesan WhatsApp maksimal 1000 karakter.';

    // Logo: unggah baru (opsional) atau hapus logo lama
    $newLogo = null;
    if (!$errors && !empty($_FILES['site_logo'])) {
        $r = save_image($_FILES['site_logo'], 'branding', 2097152);
        if ($r['ok']) $newLogo = $r['file'];
        elseif (empty($r['none'])) $errors[] = 'Logo: ' . $r['error'];
    }

    if (!$errors) {
        foreach ($f as $k => $v) set_setting($k, $v);
        $oldLogo = (string)setting('site_logo', '');
        if ($newLogo) {
            set_setting('site_logo', $newLogo);
            delete_upload($oldLogo);
        } elseif (!empty($_POST['remove_logo']) && $oldLogo !== '') {
            set_setting('site_logo', '');
            delete_upload($oldLogo);
        }
        flash('success', 'Pengaturan disimpan.');
        redirect('admin/pengaturan');
    }
}
$area = 'admin'; $active = 'settings'; $title = 'Pengaturan';
render('admin/settings', compact('f', 'errors', 'logo', 'area', 'active', 'title'), 'dash');
