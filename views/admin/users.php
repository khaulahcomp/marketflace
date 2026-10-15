<form class="toolbar" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama, username, email, toko…" aria-label="Cari pengguna">
  <select name="status" aria-label="Status">
    <option value="">Semua status</option>
    <?php foreach (['active' => 'Aktif', 'pending' => 'Menunggu', 'banned' => 'Dinonaktifkan'] as $k => $l): ?><option value="<?= e($k) ?>" <?= $st === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-primary btn-sm" type="submit">Filter</button>
</form>
<p class="muted"><?= num($total) ?> pengguna</p>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Pengguna</th><th>Kontak</th><th>Peran</th><th>Status</th><th class="r">Produk</th><th>Bergabung</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $u): $self = (int)$u['id'] === (int)$me['id']; ?>
    <tr>
      <td><strong><?= e($u['name']) ?></strong><small><a href="<?= e(url('toko/' . $u['username'])) ?>" target="_blank" rel="noopener"><?= e($u['store_name'] ?: '@' . $u['username']) ?></a></small></td>
      <td><?= e($u['email']) ?><small><?= e($u['whatsapp'] ?: '—') ?></small></td>
      <td><?= $u['role'] === 'admin' ? '<span class="badge st-published">Admin</span>' : 'Penjual' ?></td>
      <td><span class="badge us-<?= e($u['status']) ?>"><?= e(['active' => 'Aktif', 'pending' => 'Menunggu', 'banned' => 'Nonaktif'][$u['status']] ?? $u['status']) ?></span></td>
      <td class="r"><?= num($u['n_products']) ?></td>
      <td><?= e(fdate($u['created_at'])) ?></td>
      <td class="acts">
      <?php if ($self): ?><small>Akun Anda</small><?php else: ?>
        <?php
        $btns = [];
        if ($u['status'] !== 'active') $btns[] = ['activate', 'Aktifkan', 'btn-ghost', ''];
        if ($u['status'] === 'active') $btns[] = ['ban', 'Nonaktifkan', 'btn-ghost', 'Nonaktifkan akun ini? Produknya tidak akan tampil.'];
        $btns[] = $u['role'] === 'admin' ? ['make_seller', 'Jadikan penjual', 'btn-ghost', 'Cabut hak admin pengguna ini?'] : ['make_admin', 'Jadikan admin', 'btn-ghost', 'Berikan hak admin penuh kepada pengguna ini?'];
        $btns[] = ['delete', 'Hapus', 'btn-danger-ghost', 'Hapus pengguna ini beserta SEMUA produknya? Tidak bisa dibatalkan.'];
        foreach ($btns as $b): ?>
          <form method="post" class="inline" <?= $b[3] !== '' ? 'data-confirm="' . e($b[3]) . '"' : '' ?>>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="act" value="<?= e($b[0]) ?>"><input type="hidden" name="qs" value="<?= e($qs) ?>">
            <button class="btn <?= e($b[2]) ?> btn-xs" type="submit"><?= e($b[1]) ?></button>
          </form>
        <?php endforeach; ?>
      <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= pagination_html($pg) ?>
