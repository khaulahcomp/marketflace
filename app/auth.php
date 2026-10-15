<?php
/**
 * Autentikasi & otorisasi.
 */

function current_user()
{
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $u = row('SELECT * FROM users WHERE id = ?', [(int)$_SESSION['uid']]);
            if (!$u || $u['status'] !== 'active') {
                $u = null;
                unset($_SESSION['uid']);
            }
        }
    }
    return $u;
}

function is_admin(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function login_user(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
}

function logout_user(): void
{
    unset($_SESSION['uid']);
    session_regenerate_id(true);
}

function require_login(): void
{
    if (!current_user()) {
        $_SESSION['intended'] = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        flash('warn', 'Silakan masuk terlebih dahulu.');
        redirect('masuk');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        abort(403, 'Halaman ini khusus administrator.');
    }
}

/* ---------- Pembatasan percobaan login ---------- */

function login_blocked(): bool
{
    $n = (int)val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)', [client_ip()]);
    return $n >= 8;
}

function login_failed(): void
{
    q('INSERT INTO login_attempts (ip) VALUES (?)', [client_ip()]);
    q('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)');
}

function login_cleared(): void
{
    q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
}
