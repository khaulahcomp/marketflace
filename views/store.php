<?php
$storeName = $seller['store_name'] !== '' ? $seller['store_name'] : $seller['name'];
$title = 'Toko ' . $storeName;
$meta_desc = excerpt((string)$seller['store_desc'], 160);
?>
<div class="container section">
  <div class="store-head">
    <span class="avatar lg"><?php if ($seller['avatar']): ?><img src="<?= e(upload_url($seller['avatar'])) ?>" alt=""><?php else: ?><?= e(initial($storeName)) ?><?php endif; ?></span>
    <div>
      <h1 class="h-page"><?= e($storeName) ?></h1>
      <p class="muted">Bergabung sejak <?= e(fdate($seller['created_at'])) ?> · <?= num(count($items)) ?> produk tayang</p>
      <?php if ($seller['store_desc']): ?><p class="prose"><?= nl2br(e($seller['store_desc'])) ?></p><?php endif; ?>
    </div>
  </div>
  <?php if ($items): ?>
    <div class="grid" style="margin-top:28px">
      <?php foreach ($items as $p) include ROOT . '/views/partials/product_card.php'; ?>
    </div>
  <?php else: ?>
    <div class="empty"><h3>Belum ada produk tayang</h3><p>Toko ini belum memiliki produk yang dipublikasikan.</p></div>
  <?php endif; ?>
</div>
