<?php
/**
 * Koneksi database (PDO) + query helper + fungsi domain berbasis DB.
 */

function config(?string $key = null, $default = null)
{
    static $c = null;
    if ($c === null) {
        $c = require __DIR__ . '/config.php';
    }
    return $key === null ? $c : ($c[$key] ?? $default);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . config('db_host', 'localhost') . ';dbname=' . config('db_name') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, config('db_user'), config('db_pass'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        try {
            $pdo->exec("SET time_zone = '+07:00'");
        } catch (Throwable $e) {
            // abaikan jika hosting membatasi
        }
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute(array_values($params));
    return $st;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function row(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function val(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetchColumn();
    return $r === false ? null : $r;
}

function last_id(): int
{
    return (int)db()->lastInsertId();
}

/* ---------- Pengaturan situs ---------- */

function settings_all(): array
{
    static $s = null;
    if ($s === null) {
        $s = [];
        try {
            foreach (rows('SELECT skey, svalue FROM settings') as $r) {
                $s[$r['skey']] = $r['svalue'];
            }
        } catch (Throwable $e) {
        }
    }
    return $s;
}

function setting(string $key, $default = '')
{
    $s = settings_all();
    return array_key_exists($key, $s) && $s[$key] !== null ? $s[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    q('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$key, $value]);
}

/* ---------- Migrasi skema otomatis (instalasi lama) ---------- */

/**
 * Menambah kolom harga coret & flash sale pada instalasi lama (sekali saja, lalu
 * versi skema dicatat di tabel settings). Return true bila kolom siap dipakai.
 */
function flash_ready(): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if ((int)setting('schema_version', 0) >= 2) {
        return $ok = true;
    }
    try {
        $cols = array_column(rows('SHOW COLUMNS FROM products'), 'Field');
        $add = [];
        if (!in_array('compare_price', $cols, true)) $add[] = 'ADD COLUMN compare_price BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER price';
        if (!in_array('flash_price', $cols, true))   $add[] = 'ADD COLUMN flash_price BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER compare_price';
        if (!in_array('flash_start', $cols, true))   $add[] = 'ADD COLUMN flash_start DATETIME NULL AFTER flash_price';
        if (!in_array('flash_end', $cols, true))     $add[] = 'ADD COLUMN flash_end DATETIME NULL AFTER flash_start';
        if ($add) {
            db()->exec('ALTER TABLE products ' . implode(', ', $add));
        }
        $idx = rows("SHOW INDEX FROM products WHERE Key_name = 'idx_products_flash'");
        if (!$idx) {
            db()->exec('ALTER TABLE products ADD KEY idx_products_flash (flash_end)');
        }
        set_setting('schema_version', '2');
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[' . date('c') . '] Migrasi flash sale gagal: ' . $e->getMessage());
        return $ok = false;
    }
}

/* ---------- Flash sale & harga efektif ---------- */

/** Kondisi SQL: flash sale sedang berlangsung. Waktu memakai jam PHP (WIB) agar konsisten dengan data tersimpan. */
function flash_active_sql(): string
{
    $now = "'" . date('Y-m-d H:i:s') . "'";
    return "(p.flash_price > 0 AND p.price > p.flash_price AND p.flash_end > $now AND (p.flash_start IS NULL OR p.flash_start <= $now))";
}

/** Ekspresi SQL harga yang berlaku saat ini (harga flash bila aktif, jika tidak harga normal). */
function eff_price_sql(): string
{
    return flash_ready() ? '(CASE WHEN ' . flash_active_sql() . ' THEN p.flash_price ELSE p.price END)' : 'p.price';
}

/** Ringkasan flash sale aktif untuk spanduk situs: jumlah produk dan detik tersisa hingga yang paling dulu berakhir. */
function flash_summary(): ?array
{
    static $s = false;
    if ($s === false) {
        $s = null;
        if (flash_ready()) {
            $r = row('SELECT COUNT(*) AS n, MIN(p.flash_end) AS soon ' . product_from_sql() . " WHERE p.status = 'published' AND " . flash_active_sql());
            if ($r && (int)$r['n'] > 0 && !empty($r['soon'])) {
                $s = ['n' => (int)$r['n'], 'left' => max(0, strtotime($r['soon']) - time())];
            }
        }
    }
    return $s;
}

/* ---------- Produk ---------- */

function product_from_sql(): string
{
    return "FROM products p
            JOIN users u ON u.id = p.seller_id AND u.status = 'active'
            LEFT JOIN categories c ON c.id = p.category_id ";
}

function product_base_sql(): string
{
    return "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
                   u.store_name, u.username, u.whatsapp, u.name AS seller_name, u.avatar AS seller_avatar "
        . product_from_sql();
}

function unique_slug(string $table, string $slug, int $ignoreId = 0): string
{
    $base = $slug;
    $i = 1;
    while ((int)val("SELECT COUNT(*) FROM `$table` WHERE slug = ? AND id <> ?", [$slug, $ignoreId]) > 0) {
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

function delete_product(int $id): void
{
    $p = row('SELECT thumbnail FROM products WHERE id = ?', [$id]);
    if (!$p) return;
    delete_upload($p['thumbnail']);
    foreach (rows('SELECT filename FROM product_images WHERE product_id = ?', [$id]) as $i) {
        delete_upload($i['filename']);
    }
    q('DELETE FROM products WHERE id = ?', [$id]);
}

function delete_user(int $id): void
{
    foreach (rows('SELECT id FROM products WHERE seller_id = ?', [$id]) as $r) {
        delete_product((int)$r['id']);
    }
    $u = row('SELECT avatar FROM users WHERE id = ?', [$id]);
    if ($u) delete_upload($u['avatar']);
    q('DELETE FROM leads WHERE seller_id = ?', [$id]);
    q('DELETE FROM users WHERE id = ?', [$id]);
}

function all_categories(): array
{
    static $c = null;
    if ($c === null) {
        $c = rows('SELECT * FROM categories ORDER BY sort_order, name');
    }
    return $c;
}
