<?php
/**
 * Akun admin: ganti username dan password.
 * Setiap perubahan wajib memasukkan password saat ini.
 */
$u = current_user();
$errors = [];
$username = (string)$u['username'];

if (is_post()) {
    csrf_verify();
    $section = (string)($_POST['section'] ?? '');
    $cur     = (string)($_POST['current_password'] ?? '');

    // Verifikasi password saat ini (dengan pembatasan percobaan, sama seperti halaman masuk)
    if (login_blocked()) {
        $errors[] = 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
    } elseif (!password_verify($cur, (string)$u['password'])) {
        login_failed();
        $errors[] = 'Password saat ini salah.';
    }

    if (!$errors && $section === 'username') {
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        if (!preg_match('/^[a-z0-9_]{3,40}$/', $username)) {
            $errors[] = 'Username 3-40 karakter: huruf kecil, angka, atau underscore.';
        } elseif ($username === $u['username']) {
            $errors[] = 'Username baru sama dengan username saat ini.';
        } elseif ((int)val('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$username, $u['id']]) > 0) {
            $errors[] = 'Username sudah dipakai pengguna lain.';
        }
        if (!$errors) {
            try {
                q('UPDATE users SET username = ? WHERE id = ?', [$username, $u['id']]);
            } catch (Throwable $e) {
                $errors[] = 'Username sudah dipakai pengguna lain.';
            }
        }
        if (!$errors) {
            login_cleared();
            flash('success', 'Username diganti menjadi "' . $username . '". Gunakan username baru ini saat masuk.');
            redirect('admin/akun');
        }
    } elseif (!$errors && $section === 'password') {
        $new  = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password2'] ?? '');
        if (strlen($new) < 8) $errors[] = 'Password baru minimal 8 karakter.';
        if (strlen($new) > 72) $errors[] = 'Password baru maksimal 72 karakter.';
        if ($new !== $new2) $errors[] = 'Konfirmasi password baru tidak sama.';
        if ($new === $cur) $errors[] = 'Password baru harus berbeda dari password saat ini.';
        if (!$errors) {
            q('UPDATE users SET password = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            session_regenerate_id(true);
            login_cleared();
            flash('success', 'Password berhasil diganti.');
            redirect('admin/akun');
        }
    } elseif (!$errors) {
        $errors[] = 'Permintaan tidak dikenali.';
    }
}

$area = 'admin'; $active = 'account'; $title = 'Akun admin';
render('admin/account', compact('u', 'username', 'errors', 'area', 'active', 'title'), 'dash');
