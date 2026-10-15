<?php
$u = current_user();
$errors = [];
$f = [
    'name' => $u['name'], 'store_name' => $u['store_name'],
    'store_desc' => (string)$u['store_desc'], 'whatsapp' => $u['whatsapp'],
];

if (is_post()) {
    csrf_verify();
    $section = (string)($_POST['section'] ?? 'profile');

    if ($section === 'password') {
        $cur  = (string)($_POST['current_password'] ?? '');
        $new  = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password2'] ?? '');
        if (!password_verify($cur, $u['password'])) $errors[] = 'Password saat ini salah.';
        if (strlen($new) < 8) $errors[] = 'Password baru minimal 8 karakter.';
        if ($new !== $new2) $errors[] = 'Konfirmasi password baru tidak sama.';
        if (!$errors) {
            q('UPDATE users SET password = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            session_regenerate_id(true);
            flash('success', 'Password berhasil diganti.');
            redirect('dashboard/profil');
        }
    } else {
        foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));
        $wa = normalize_wa($f['whatsapp']);
        if (strlen($f['name']) < 3 || strlen($f['name']) > 100) $errors[] = 'Nama lengkap 3-100 karakter.';
        if (strlen($f['store_name']) < 3 || strlen($f['store_name']) > 120) $errors[] = 'Nama toko 3-120 karakter.';
        if (!valid_wa($wa)) $errors[] = 'Nomor WhatsApp tidak valid. Contoh: 081234567890.';
        if (strlen($f['store_desc']) > 1000) $errors[] = 'Deskripsi toko maksimal 1000 karakter.';

        $avatar = null;
        if (!$errors && !empty($_FILES['avatar'])) {
            $r = save_image($_FILES['avatar'], 'avatars', 1048576);
            if ($r['ok']) $avatar = $r['file'];
            elseif (empty($r['none'])) $errors[] = 'Logo: ' . $r['error'];
        }
        if (!$errors) {
            $sql = 'UPDATE users SET name=?, store_name=?, store_desc=?, whatsapp=?';
            $par = [$f['name'], $f['store_name'], $f['store_desc'], $wa];
            if ($avatar) { $sql .= ', avatar=?'; $par[] = $avatar; }
            $par[] = $u['id'];
            q($sql . ' WHERE id=?', $par);
            if ($avatar) delete_upload($u['avatar']);
            flash('success', 'Profil toko diperbarui.');
            redirect('dashboard/profil');
        }
    }
}
$area = 'seller'; $active = 'profile'; $title = 'Profil & toko';
render('dash/profile', compact('u', 'f', 'errors', 'area', 'active', 'title'), 'dash');
