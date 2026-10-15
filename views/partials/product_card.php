<?php /** @var array $p */ ?>
<article class="pcard">
  <a class="pframe" href="<?= e(url('produk/' . $p['slug'])) ?>" tabindex="-1" aria-hidden="true">
    <div class="chrome"><i></i><i></i><i></i><span class="url"><?= e(demo_host($p)) ?></span></div>
    <div class="shot"><?= thumb_html($p) ?></div>
    <?php if (!empty($p['demo_url'])): ?><span class="demo-pill"><?= ico_demo(13) ?> Demo live</span><?php endif; ?>
    <?php if (!empty($p['is_featured'])): ?><span class="feat-pill">Pilihan</span><?php endif; ?>
  </a>
  <div class="pbody">
    <div class="pmeta">
      <span><?= e(product_types()[$p['type']] ?? 'Produk') ?></span>
      <?php if (!empty($p['cat_name'])): ?><span><?= e($p['cat_name']) ?></span><?php endif; ?>
    </div>
    <h3 class="ptitle"><a href="<?= e(url('produk/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
    <p class="psd"><?= e(excerpt($p['short_desc'], 90)) ?></p>
    <div class="pfoot">
      <strong class="price"><?= e(rupiah($p['price'])) ?></strong>
      <a class="seller" href="<?= e(url('toko/' . $p['username'])) ?>"><?= e($p['store_name'] !== '' ? $p['store_name'] : $p['seller_name']) ?></a>
    </div>
    <div class="pact">
      <?php if (!empty($p['demo_url'])): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('demo/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= ico_demo(15) ?> Demo</a>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('produk/' . $p['slug'])) ?>">Detail</a>
      <?php endif; ?>
      <a class="btn btn-wa btn-sm" href="<?= e(url('go/wa/' . $p['slug'])) ?>" target="_blank" rel="noopener nofollow"><?= wa_icon(15) ?> Beli</a>
    </div>
  </div>
</article>
