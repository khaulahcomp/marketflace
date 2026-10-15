<?php
$p = row(product_base_sql() . ' WHERE p.slug = ? LIMIT 1', [$slug]);
if (!$p) abort(404, 'Produk tidak ditemukan.');

$me = current_user();
$isOwner = $me && (int)$me['id'] === (int)$p['seller_id'];
if ($p['status'] !== 'published' && !($me && ($me['role'] === 'admin' || $isOwner))) abort(404, 'Produk tidak ditemukan.');

$wa = normalize_wa((string)$p['whatsapp']);
if (!valid_wa($wa)) {
    flash('error', 'Penjual belum mengisi nomor WhatsApp yang valid.');
    redirect('produk/' . $p['slug']);
}

// catat prospek (maks 1 kali per IP per produk per jam), lewati jika pemilik sendiri
if ($p['status'] === 'published' && !$isOwner) {
    q('UPDATE products SET wa_clicks = wa_clicks + 1 WHERE id = ?', [$p['id']]);
    $recent = (int)val('SELECT COUNT(*) FROM leads WHERE product_id = ? AND ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)', [$p['id'], client_ip()]);
    if ($recent === 0) {
        q('INSERT INTO leads (product_id, seller_id, ip) VALUES (?,?,?)', [$p['id'], $p['seller_id'], client_ip()]);
    }
}

header('Cache-Control: no-store');
redirect('https://wa.me/' . $wa . '?text=' . rawurlencode(wa_message($p)));
