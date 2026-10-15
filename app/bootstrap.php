<?php
/**
 * Bootstrap: dimuat oleh index.php. Menyiapkan error handling, session, DB, dan helper.
 */
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Asia/Jakarta');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

require __DIR__ . '/helpers.php';

if (!is_file(__DIR__ . '/config.php')) {
    header('Location: ' . url('install.php'));
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$__debug = (bool)config('debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $__debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');

set_exception_handler(function ($e) use ($__debug) {
    error_log('[' . date('c') . '] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if ($__debug) {
        echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
    } else {
        echo '<!doctype html><meta charset="utf-8"><title>Terjadi kesalahan</title>'
            . '<body style="font-family:sans-serif;max-width:480px;margin:15vh auto;padding:0 20px">'
            . '<h1>Terjadi kesalahan</h1><p>Maaf, ada masalah di server. Silakan coba lagi beberapa saat lagi.</p></body>';
    }
    exit;
});

if (PHP_SAPI !== 'cli') {
    session_name('mpsid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() === '' ? '/' : base_path() . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
