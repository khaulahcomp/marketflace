<?php
/**
 * INSTALLER - jalankan sekali lewat browser: https://domainanda.com/install.php
 * Setelah selesai, file ini akan mencoba menghapus dirinya sendiri.
 */
define('ROOT', __DIR__);
require __DIR__ . '/app/helpers.php';
session_start();

$configFile = __DIR__ . '/app/config.php';
$installed  = is_file($configFile);

/* ---------- pemeriksaan sistem ---------- */
$checks = [
    ['PHP 7.4 atau lebih baru (terdeteksi ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '7.4.0', '>='), true],
    ['Ekstensi PDO MySQL', extension_loaded('pdo_mysql'), true],
    ['Ekstensi mbstring', extension_loaded('mbstring'), false],
    ['Ekstensi GD (perkecil gambar otomatis)', extension_loaded('gd'), false],
    ['Folder app/ dapat ditulis', is_writable(__DIR__ . '/app'), true],
    ['Folder uploads/ dapat ditulis', is_writable(__DIR__ . '/uploads'), true],
    ['File schema.sql tersedia', is_file(__DIR__ . '/schema.sql'), true],
];
$canInstall = true;
foreach ($checks as $c) {
    if ($c[2] && !$c[1]) $canInstall = false;
}

$errors = [];
$done   = false;
$deleted = false;
$f = [
    'db_host' => 'localhost', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
    'site_name' => 'Marketplace Digital',
    'admin_name' => '', 'admin_username' => 'admin', 'admin_email' => '', 'admin_wa' => '',
];

if (!$installed && $canInstall && is_post()) {
    if (empty($_SESSION['_csrf']) || empty($_POST['_csrf']) || !hash_equals((string)$_SESSION['_csrf'], (string)$_POST['_csrf'])) {
        $errors[] = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    }
    foreach ($f as $k => $_) {
        $f[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $adminWa   = normalize_wa($f['admin_wa']);

    if ($f['db_name'] === '' || $f['db_user'] === '') $errors[] = 'Nama database dan user database wajib diisi.';
    if ($f['site_name'] === '') $errors[] = 'Nama situs wajib diisi.';
    if (strlen($f['admin_name']) < 3) $errors[] = 'Nama admin minimal 3 karakter.';
    if (!preg_match('/^[a-z0-9_]{3,40}$/', $f['admin_username'])) $errors[] = 'Username admin: 3-40 karakter, huruf kecil/angka/underscore.';
    if (!filter_var($f['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email admin tidak valid.';
    if (strlen($adminPass) < 8) $errors[] = 'Password admin minimal 8 karakter.';
    if ($adminWa !== '' && !valid_wa($adminWa)) $errors[] = 'Nomor WhatsApp admin tidak valid.';

    if (!$errors) {
        try {
            $pdo = new PDO(
                'mysql:host=' . $f['db_host'] . ';dbname=' . $f['db_name'] . ';charset=utf8mb4',
                $f['db_user'],
                (string)($_POST['db_pass'] ?? ''),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
            try { $pdo->exec("SET time_zone = '+07:00'"); } catch (Throwable $e) {}

            // jalankan schema.sql
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);
            foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt !== '') $pdo->exec($stmt);
            }

            if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
                throw new RuntimeException('Database ini sudah berisi data pengguna. Gunakan database yang masih kosong.');
            }

            // admin
            $pdo->prepare('INSERT INTO users (name, username, email, password, role, status, store_name, whatsapp) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$f['admin_name'], $f['admin_username'], $f['admin_email'], password_hash($adminPass, PASSWORD_DEFAULT), 'admin', 'active', $f['site_name'], $adminWa]);

            // pengaturan awal
            $defaults = [
                'site_name'         => $f['site_name'],
                'tagline'           => 'Software & website siap pakai. Coba demonya, lalu chat penjualnya.',
                'site_description'  => 'Marketplace produk digital: software, website, template, script, dan plugin. Lihat demo langsung dan beli lewat WhatsApp penjual.',
                'contact_email'     => $f['admin_email'],
                'footer_text'       => '© ' . date('Y') . ' ' . $f['site_name'] . '. Transaksi dilakukan langsung antara pembeli dan penjual.',
                'accent_color'      => '#1f4fff',
                'allow_registration'=> '1',
                'seller_approval'   => '0',
                'moderate_products' => '1',
                'schema_version'    => '2',
                'wa_message'        => "Halo {toko}, saya tertarik dengan produk *{produk}* ({harga}) yang saya lihat di {situs}.\n\nLink: {link}\n\nApakah masih tersedia?",
            ];
            $st = $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)');
            foreach ($defaults as $k => $v) $st->execute([$k, $v]);

            // kategori awal
            $cats = [
                ['Aplikasi Desktop', 'aplikasi-desktop', '🖥️'],
                ['Website & Template', 'website-template', '🌐'],
                ['Script PHP', 'script-php', '🧩'],
                ['Plugin & Addon', 'plugin-addon', '🔌'],
                ['Aplikasi Mobile', 'aplikasi-mobile', '📱'],
                ['Sistem Informasi', 'sistem-informasi', '🏫'],
                ['UI Kit & Desain', 'ui-kit-desain', '🎨'],
                ['Lainnya', 'lainnya', '📦'],
            ];
            $st = $pdo->prepare('INSERT IGNORE INTO categories (name, slug, icon, sort_order) VALUES (?,?,?,?)');
            foreach ($cats as $i => $c) $st->execute([$c[0], $c[1], $c[2], $i + 1]);

            // tulis config
            $cfg = [
                'db_host' => $f['db_host'],
                'db_name' => $f['db_name'],
                'db_user' => $f['db_user'],
                'db_pass' => (string)($_POST['db_pass'] ?? ''),
                'debug'   => false,
            ];
            $php = "<?php\n// Dibuat otomatis oleh install.php pada " . date('c') . "\nreturn " . var_export($cfg, true) . ";\n";
            if (file_put_contents($configFile, $php) === false) {
                throw new RuntimeException('Tidak bisa menulis app/config.php. Periksa izin folder app/.');
            }
            @chmod($configFile, 0644);

            foreach (['uploads/products', 'uploads/avatars', 'uploads/branding'] as $d) {
                if (!is_dir(__DIR__ . '/' . $d)) @mkdir(__DIR__ . '/' . $d, 0755, true);
            }
            $done = true;
            $installed = true;
            $deleted = @unlink(__FILE__);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if ($e instanceof PDOException) {
                $msg = 'Gagal terhubung/menyiapkan database: ' . $msg;
            }
            $errors[] = $msg;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Pasang Marketplace Digital</title>
<style>
  :root{--ink:#0f1a2e;--muted:#5f6b80;--line:#dde3ee;--bg:#eef1f7;--accent:#1f4fff}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.55 "Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
  main{max-width:640px;margin:0 auto;padding:40px 20px 80px}
  h1{font-size:28px;letter-spacing:-.02em;margin:0 0 6px}
  h2{font-size:17px;margin:28px 0 10px}
  p.lead{color:var(--muted);margin:0 0 24px}
  .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:22px}
  label{display:block;font-weight:600;margin:14px 0 5px;font-size:14px}
  input{width:100%;padding:11px 12px;border:1px solid var(--line);border-radius:9px;font:inherit;background:#fff}
  input:focus{outline:2px solid var(--accent);outline-offset:1px;border-color:transparent}
  small{color:var(--muted)}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  @media(max-width:520px){.row{grid-template-columns:1fr}}
  button{margin-top:24px;width:100%;padding:13px;border:0;border-radius:10px;background:var(--accent);color:#fff;font:inherit;font-weight:700;cursor:pointer}
  button:hover{filter:brightness(1.08)}
  .err{background:#fdecec;border:1px solid #f4bcbc;color:#8a1f1f;padding:12px 14px;border-radius:10px;margin-bottom:16px}
  .ok{background:#e8f7ee;border:1px solid #b5e3c6;color:#14532d;padding:14px;border-radius:10px}
  .warn{background:#fff6e0;border:1px solid #f0d78a;color:#6b4a00;padding:14px;border-radius:10px;margin-top:14px}
  ul.chk{list-style:none;padding:0;margin:0}
  ul.chk li{padding:5px 0;display:flex;gap:8px}
  .y{color:#15803d}.n{color:#b91c1c}.o{color:#b45309}
  a.btn{display:inline-block;margin-top:16px;padding:11px 18px;border-radius:10px;background:var(--accent);color:#fff;text-decoration:none;font-weight:700}
</style>
</head>
<body>
<main>
  <h1>Pasang Marketplace Digital</h1>
  <p class="lead">Isi data database dan akun admin. Proses ini hanya dilakukan sekali.</p>

<?php if ($done): ?>
  <div class="card">
    <div class="ok"><strong>Instalasi berhasil.</strong> Tabel database, akun admin, kategori, dan pengaturan awal sudah dibuat.</div>
    <?php if ($deleted): ?>
      <div class="warn">File <code>install.php</code> sudah dihapus otomatis.</div>
    <?php else: ?>
      <div class="warn"><strong>Penting:</strong> hapus file <code>install.php</code> dari File Manager cPanel sekarang juga.</div>
    <?php endif; ?>
    <a class="btn" href="<?= e(url('masuk')) ?>">Masuk sebagai admin</a>
  </div>

<?php elseif ($installed): ?>
  <div class="card">
    <div class="warn"><strong>Aplikasi sudah terpasang.</strong> Hapus <code>install.php</code> dari server. Untuk memasang ulang, hapus dulu <code>app/config.php</code> dan kosongkan database.</div>
    <a class="btn" href="<?= e(url('')) ?>">Ke beranda</a>
  </div>

<?php else: ?>
  <div class="card">
    <h2 style="margin-top:0">Pemeriksaan server</h2>
    <ul class="chk">
      <?php foreach ($checks as $c): ?>
        <li><span class="<?= $c[1] ? 'y' : ($c[2] ? 'n' : 'o') ?>"><?= $c[1] ? '✔' : ($c[2] ? '✘' : '!') ?></span> <?= e($c[0]) ?><?= (!$c[1] && !$c[2]) ? ' <small>(disarankan)</small>' : '' ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if (!$canInstall): ?>
    <div class="err" style="margin-top:16px">Perbaiki item bertanda ✘ terlebih dahulu (di cPanel: Select PHP Version / File Manager → izin folder 755).</div>
  <?php else: ?>
  <form method="post" class="card" style="margin-top:16px" autocomplete="off">
    <?= csrf_field() ?>
    <?php if ($errors): ?><div class="err"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>

    <h2 style="margin-top:0">1. Database MySQL</h2>
    <small>Buat database dan user lebih dulu lewat <em>MySQL® Databases</em> di cPanel, lalu hubungkan keduanya dengan <em>All Privileges</em>.</small>
    <label>Host database</label>
    <input name="db_host" value="<?= e($f['db_host']) ?>" required>
    <div class="row">
      <div><label>Nama database</label><input name="db_name" value="<?= e($f['db_name']) ?>" placeholder="cpuser_market" required></div>
      <div><label>User database</label><input name="db_user" value="<?= e($f['db_user']) ?>" placeholder="cpuser_dbuser" required></div>
    </div>
    <label>Password database</label>
    <input type="password" name="db_pass" value="">

    <h2>2. Situs</h2>
    <label>Nama situs</label>
    <input name="site_name" value="<?= e($f['site_name']) ?>" required>

    <h2>3. Akun administrator</h2>
    <div class="row">
      <div><label>Nama lengkap</label><input name="admin_name" value="<?= e($f['admin_name']) ?>" required></div>
      <div><label>Username</label><input name="admin_username" value="<?= e($f['admin_username']) ?>" required></div>
    </div>
    <label>Email</label>
    <input type="email" name="admin_email" value="<?= e($f['admin_email']) ?>" required>
    <label>Nomor WhatsApp (opsional)</label>
    <input name="admin_wa" value="<?= e($f['admin_wa']) ?>" placeholder="08123456789">
    <label>Password (min. 8 karakter)</label>
    <input type="password" name="admin_pass" minlength="8" required>

    <button type="submit">Pasang sekarang</button>
  </form>
  <?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
