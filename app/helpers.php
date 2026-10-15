<?php
/**
 * Fungsi pembantu umum (tanpa akses database).
 * Kompatibel PHP 7.4 s/d 8.x
 */

/* ---------- Output & URL ---------- */

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    static $b = null;
    if ($b === null) {
        $d = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $b = ($d === '/' || $d === '.' || $d === '\\') ? '' : rtrim($d, '/');
    }
    return $b;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
}

function site_origin(): string
{
    $host = preg_replace('/[^a-zA-Z0-9\.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return (is_https() ? 'https' : 'http') . '://' . $host;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function abs_url(string $path = ''): string
{
    return site_origin() . url($path);
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . ltrim($path, '/')) . $v;
}

function upload_url(?string $file): string
{
    return $file ? url('uploads/' . ltrim($file, '/')) : '';
}

function redirect(string $path): void
{
    $target = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $target);
    exit;
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/* ---------- Template ---------- */

function render(string $view, array $data = [], string $layout = 'main'): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require ROOT . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require ROOT . '/views/layouts/' . $layout . '.php';
}

function abort(int $code = 404, string $msg = ''): void
{
    http_response_code($code);
    render('error', ['code' => $code, 'msg' => $msg]);
    exit;
}

/* ---------- Flash message ---------- */

function flash(string $type, string $msg): void
{
    $_SESSION['_flash'][] = ['t' => $type, 'm' => $msg];
}

function flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $t = $_POST['_csrf'] ?? '';
    $s = $_SESSION['_csrf'] ?? '';
    if (!is_string($t) || !is_string($s) || $t === '' || $s === '' || !hash_equals($s, $t)) {
        http_response_code(419);
        render('error', ['code' => 419, 'msg' => 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.']);
        exit;
    }
}

/* ---------- Format ---------- */

function rupiah($n): string
{
    $n = (float)$n;
    return $n <= 0 ? 'Hubungi penjual' : 'Rp ' . number_format($n, 0, ',', '.');
}

function fdate($d, bool $time = false): string
{
    $t = strtotime((string)$d);
    if (!$t) {
        return '-';
    }
    $m = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('j', $t) . ' ' . $m[(int)date('n', $t) - 1] . ' ' . date('Y', $t) . ($time ? date(' H:i', $t) : '');
}

function time_ago($d): string
{
    $t = strtotime((string)$d);
    if (!$t) {
        return '-';
    }
    $diff = time() - $t;
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return fdate($d);
}

function num($n): string
{
    return number_format((float)$n, 0, ',', '.');
}

function excerpt(string $s, int $len = 120): string
{
    $s = trim(preg_replace('/\s+/', ' ', strip_tags($s)));
    if (function_exists('mb_strlen')) {
        return mb_strlen($s) > $len ? mb_substr($s, 0, $len - 1) . '…' : $s;
    }
    return strlen($s) > $len ? substr($s, 0, $len - 1) . '…' : $s;
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false && $t !== '') {
            $s = strtolower($t);
        }
    }
    $s = trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
    return $s !== '' ? substr($s, 0, 80) : 'item';
}

function initial(string $s): string
{
    $s = trim($s);
    if ($s === '') return '?';
    return function_exists('mb_substr') ? mb_strtoupper(mb_substr($s, 0, 1)) : strtoupper($s[0]);
}

/* ---------- Domain: tipe produk, WhatsApp ---------- */

function product_types(): array
{
    return [
        'software' => 'Software / Aplikasi',
        'website'  => 'Website / Template',
        'script'   => 'Script / Source Code',
        'plugin'   => 'Plugin / Addon',
        'mobile'   => 'Aplikasi Mobile',
        'other'    => 'Lainnya',
    ];
}

function status_labels(): array
{
    return ['draft' => 'Draft', 'pending' => 'Menunggu review', 'published' => 'Tayang', 'rejected' => 'Ditolak'];
}

function status_badge(string $s): string
{
    $l = status_labels()[$s] ?? $s;
    return '<span class="badge st-' . e($s) . '">' . e($l) . '</span>';
}

/** Ubah 0812-xxxx / +62812xxx / 812xxx menjadi 62812xxx */
function normalize_wa(string $n): string
{
    $d = preg_replace('/\D+/', '', $n);
    if ($d === '') return '';
    if (strpos($d, '00') === 0) $d = substr($d, 2);
    if (strpos($d, '0') === 0) return '62' . substr($d, 1);
    if (strpos($d, '8') === 0) return '62' . $d;
    return $d;
}

function valid_wa(string $d): bool
{
    return (bool)preg_match('/^[1-9][0-9]{8,14}$/', $d);
}

function valid_demo_url(string $u): bool
{
    return strlen($u) <= 255 && preg_match('#^https?://#i', $u) && filter_var($u, FILTER_VALIDATE_URL) !== false;
}

/* ---------- Upload gambar ---------- */

function upload_dir(): string
{
    return ROOT . '/uploads';
}

function files_array(string $key): array
{
    $out = [];
    if (empty($_FILES[$key]) || !is_array($_FILES[$key]['name'])) {
        return $out;
    }
    foreach ($_FILES[$key]['name'] as $i => $n) {
        $out[] = [
            'name'     => $n,
            'type'     => $_FILES[$key]['type'][$i],
            'tmp_name' => $_FILES[$key]['tmp_name'][$i],
            'error'    => $_FILES[$key]['error'][$i],
            'size'     => $_FILES[$key]['size'][$i],
        ];
    }
    return $out;
}

/**
 * Simpan gambar upload dengan validasi ketat dan encode ulang (GD) agar aman.
 * Return: ['ok'=>bool, 'file'=>'products/xxx.jpg', 'error'=>'', 'none'=>bool]
 */
function save_image(array $file, string $sub = 'products', int $maxBytes = 2097152): array
{
    $err = function ($m) {
        return ['ok' => false, 'error' => $m];
    };
    if (!isset($file['error']) || is_array($file['error'])) {
        return $err('Berkas tidak valid.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => '', 'none' => true];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $err('Upload gagal. Pastikan ukuran gambar maksimal 2 MB.');
    }
    if ($file['size'] > $maxBytes) {
        return $err('Ukuran gambar maksimal ' . round($maxBytes / 1048576) . ' MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return $err('Berkas tidak valid.');
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        return $err('Berkas bukan gambar yang valid.');
    }
    $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $info['mime'];
    if (!isset($exts[$mime])) {
        return $err('Format gambar harus JPG, PNG, atau WEBP.');
    }
    if ($info[0] > 8000 || $info[1] > 8000) {
        return $err('Dimensi gambar terlalu besar (maks 8000 px).');
    }
    $dir = upload_dir() . '/' . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = $sub . '/' . bin2hex(random_bytes(12)) . '.' . $exts[$mime];
    $dest = upload_dir() . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return $err('Gagal menyimpan gambar. Periksa izin folder uploads/ (755).');
    }
    @chmod($dest, 0644);
    resize_image($dest, $mime, $sub === 'avatars' ? 400 : ($sub === 'branding' ? 800 : 1280));
    return ['ok' => true, 'file' => $name];
}

/** Perkecil + encode ulang gambar (menghapus payload tersembunyi). Aman jika GD tidak ada. */
function resize_image(string $path, string $mime, int $maxW): void
{
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }
    $info = @getimagesize($path);
    if (!$info) return;
    [$w, $h] = $info;
    switch ($mime) {
        case 'image/jpeg':
            $src = @imagecreatefromjpeg($path);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($path);
            break;
        case 'image/webp':
            $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            break;
        default:
            $src = false;
    }
    if (!$src) return;
    $nw = min($w, $maxW);
    $nh = (int)round($h * ($nw / $w));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime !== 'image/jpeg') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    if ($mime === 'image/jpeg') {
        imagejpeg($dst, $path, 85);
    } elseif ($mime === 'image/png') {
        imagepng($dst, $path, 6);
    } elseif (function_exists('imagewebp')) {
        imagewebp($dst, $path, 85);
    }
    imagedestroy($src);
    imagedestroy($dst);
}

function delete_upload(?string $rel): void
{
    if (!$rel || !preg_match('#^(products|avatars|branding)/[a-f0-9]{24}\.(jpg|png|webp)$#', $rel)) {
        return;
    }
    $p = upload_dir() . '/' . $rel;
    if (is_file($p)) {
        @unlink($p);
    }
}

/* ---------- UI kecil ---------- */

function thumb_html(array $p, string $class = ''): string
{
    if (!empty($p['thumbnail'])) {
        return '<img class="' . e($class) . '" src="' . e(upload_url($p['thumbnail'])) . '" alt="' . e($p['title']) . '" loading="lazy">';
    }
    $hue = crc32((string)($p['title'] ?? 'x')) % 360;
    return '<div class="ph ' . e($class) . '" style="--hue:' . $hue . '"><span>' . e(initial((string)($p['title'] ?? '?'))) . '</span></div>';
}

function paginate(int $total, int $per, int $page): array
{
    $pages = max(1, (int)ceil($total / max(1, $per)));
    $page = min(max(1, $page), $pages);
    return ['total' => $total, 'per' => $per, 'pages' => $pages, 'page' => $page, 'offset' => ($page - 1) * $per];
}

function page_url(int $p): string
{
    $q = $_GET;
    $q['page'] = $p;
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $path . '?' . http_build_query($q);
}

function pagination_html(array $pg): string
{
    if ($pg['pages'] <= 1) return '';
    $h = '<nav class="pagination" aria-label="Halaman">';
    if ($pg['page'] > 1) {
        $h .= '<a href="' . e(page_url($pg['page'] - 1)) . '" rel="prev">‹ Sebelumnya</a>';
    }
    $from = max(1, $pg['page'] - 2);
    $to = min($pg['pages'], $pg['page'] + 2);
    for ($i = $from; $i <= $to; $i++) {
        $h .= $i === $pg['page']
            ? '<span class="current" aria-current="page">' . $i . '</span>'
            : '<a href="' . e(page_url($i)) . '">' . $i . '</a>';
    }
    if ($pg['page'] < $pg['pages']) {
        $h .= '<a href="' . e(page_url($pg['page'] + 1)) . '" rel="next">Berikutnya ›</a>';
    }
    return $h . '</nav>';
}

/* ---------- Ikon SVG & potongan UI ---------- */

function wa_icon(int $size = 18): string
{
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.15h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.7 8.23-8.23 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.23-.16-.48-.29Z"/></svg>';
}

function ico_demo(int $size = 16): string
{
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18"/><path d="M10 13l3 1.5-3 1.5z" fill="currentColor"/></svg>';
}

function logo_mark(int $size = 26): string
{
    return '<svg class="mark" width="' . $size . '" height="' . $size . '" viewBox="0 0 32 32" aria-hidden="true"><rect x="2" y="5" width="28" height="22" rx="5" fill="currentColor"/><circle cx="7.5" cy="10" r="1.4" fill="#fff" opacity=".9"/><circle cx="12" cy="10" r="1.4" fill="#fff" opacity=".6"/><path d="M11 16.5l4 3 6-6" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/** Berkas logo situs (path relatif di uploads/) jika ada dan valid, selain itu string kosong */
function site_logo_file(): string
{
    $f = (string)setting('site_logo', '');
    if ($f !== '' && preg_match('#^branding/[a-f0-9]{24}\.(jpg|png|webp)$#', $f) && is_file(upload_dir() . '/' . $f)) {
        return $f;
    }
    return '';
}

/** Isi tautan merek (logo + nama situs). Memakai logo unggahan admin bila ada, jika tidak ikon bawaan. */
function brand_inner(int $size = 26): string
{
    $name = (string)setting('site_name', 'Marketplace Digital');
    $logo = site_logo_file();
    if ($logo === '') {
        return logo_mark($size) . '<span>' . e($name) . '</span>';
    }
    $showName = setting('logo_show_name', '1') === '1';
    return '<img class="brand-logo" src="' . e(upload_url($logo)) . '" alt="' . ($showName ? '' : e($name)) . '">'
        . ($showName ? '<span>' . e($name) . '</span>' : '');
}

/** Host demo untuk ditampilkan di "address bar" kartu produk */
function demo_host(array $p): string
{
    if (!empty($p['demo_url'])) {
        $h = parse_url($p['demo_url'], PHP_URL_HOST);
        if ($h) return preg_replace('/^www\./', '', $h);
    }
    return 'belum-ada-demo';
}

/** Susun pesan WhatsApp dari template di pengaturan */
function wa_message(array $p): string
{
    $tpl = (string)setting('wa_message', "Halo {toko}, saya tertarik dengan produk *{produk}* ({harga}) di {situs}.\n\nLink: {link}");
    return strtr($tpl, [
        '{toko}'   => $p['store_name'] !== '' ? $p['store_name'] : $p['seller_name'],
        '{produk}' => $p['title'],
        '{harga}'  => rupiah($p['price']),
        '{situs}'  => setting('site_name', 'Marketplace'),
        '{link}'   => abs_url('produk/' . $p['slug']),
    ]);
}
