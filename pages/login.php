<?php
if (current_user()) redirect('dashboard');

$errors = [];
$login  = '';
if (is_post()) {
    csrf_verify();
    $login = trim((string)($_POST['login'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if (login_blocked()) {
        $errors[] = 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
    } else {
        $u = row('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1', [$login, $login]);
        $hash = $u ? $u['password'] : '$2y$10$usesomesillystringforsalt.invalidhashvalue000000000000000000';
        $ok = password_verify($pass, $hash) && $u;
        if (!$ok) {
            login_failed();
            $errors[] = 'Email/username atau password salah.';
        } elseif ($u['status'] === 'banned') {
            $errors[] = 'Akun ini dinonaktifkan. Hubungi administrator.';
        } elseif ($u['status'] === 'pending') {
            $errors[] = 'Akun Anda masih menunggu persetujuan administrator.';
        } else {
            login_cleared();
            if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
                q('UPDATE users SET password = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            }
            login_user($u);
            $to = $_SESSION['intended'] ?? '';
            unset($_SESSION['intended']);
            if ($to !== '' && strpos($to, '//') === false && strpos($to, base_path() . '/') === 0) {
                redirect(site_origin() . $to);
            }
            redirect($u['role'] === 'admin' ? 'admin' : 'dashboard');
        }
    }
}
render('login', ['errors' => $errors, 'login' => $login, 'noindex' => true]);
