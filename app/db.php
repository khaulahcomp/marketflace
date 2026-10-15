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
