<?php
if (current_user()) redirect('dashboard');
if (setting('allow_registration', '1') !== '1') abort(403, 'Pendaftaran penjual baru sedang ditutup.');

$errors = [];
$f = ['name' => '', 'store_name' => '', 'username' => '', 'email' => '', 'whatsapp' => ''];
if (is_post()) {
    csrf_verify();
    foreach ($f as $k => $_) {
        $f[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $f['username'] = strtolower($f['username']);
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');
    $wa    = normalize_wa($f['whatsapp']);

    if (!empty($_POST['website'])) { // honeypot
        redirect('');
    }
    if (strlen($f['name']) < 3 || strlen($f['name']) > 100) $errors[] = 'Nama lengkap 3-100 karakter.';
    if (strlen($f['store_name']) < 3 || strlen($f['store_name']) > 120) $errors[] = 'Nama toko 3-120 karakter.';
    if (!preg_match('/^[a-z0-9_]{3,40}$/', $f['username'])) $errors[] = 'Username 3-40 karakter: huruf kecil, angka, atau underscore.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen($f['email']) > 190) $errors[] = 'Format email tidak valid.';
    if (!valid_wa($wa)) $errors[] = 'Nomor WhatsApp tidak valid. Contoh: 081234567890.';
    if (strlen($pass) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($pass !== $pass2) $errors[] = 'Konfirmasi password tidak sama.';
    if (empty($_POST['agree'])) $errors[] = 'Anda harus menyetujui ketentuan penjual.';

    if (!$errors) {
        if ((int)val('SELECT COUNT(*) FROM users WHERE username = ?', [$f['username']]) > 0) $errors[] = 'Username sudah dipakai.';
        if ((int)val('SELECT COUNT(*) FROM users WHERE email = ?', [$f['email']]) > 0) $errors[] = 'Email sudah terdaftar.';
    }
    if (!$errors) {
        $status = setting('seller_approval', '0') === '1' ? 'pending' : 'active';
        q('INSERT INTO users (name, username, email, password, role, status, store_name, whatsapp) VALUES (?,?,?,?,?,?,?,?)',
            [$f['name'], $f['username'], $f['email'], password_hash($pass, PASSWORD_DEFAULT), 'seller', $status, $f['store_name'], $wa]);
        if ($status === 'active') {
            $u = row('SELECT * FROM users WHERE id = ?', [last_id()]);
            login_user($u);
            flash('success', 'Akun berhasil dibuat. Ayo tambahkan produk pertama Anda.');
            redirect('dashboard/produk/tambah');
        }
        flash('success', 'Pendaftaran berhasil. Akun Anda akan aktif setelah disetujui administrator.');
        redirect('masuk');
    }
}
render('register', ['errors' => $errors, 'f' => $f, 'noindex' => true]);
