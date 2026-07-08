<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" href="<?= !empty($restaurant['logo_path']) ? e($restaurant['logo_path']) : '/assets/favicon.svg' ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php include OM_ROOT . '/app/views/menu/_theme_style.php'; ?>
</head>
<body class="site-public">
<?php
$catAnchors = [];
$catIdx = 0;
if (!empty($featured)) { $catAnchors['cat-featured'] = e(t('featured')); }
foreach ($grouped as $categoryName => $items) { $catAnchors['cat-' . $catIdx++] = $categoryName; }
?>
<div class="page-header">
    <?php include OM_ROOT . '/app/views/menu/_nav.php'; ?>
    <div class="page-header-content">
        <h1><?= e(t('nav.menu')) ?></h1>
    </div>
</div>

<?php if (count($catAnchors) > 1): ?>
    <div class="category-nav-wrap">
        <div class="category-nav">
            <?php foreach ($catAnchors as $anchorId => $label): ?>
                <a href="#<?= e($anchorId) ?>" data-cat-link="<?= e($anchorId) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="menu-public">
    <?php if (!empty($featured)): ?>
        <div class="menu-category featured-category" id="cat-featured">
            <h2><?= menuIcon('star') ?> <?= e(t('featured')) ?></h2>
            <?php foreach ($featured as $item): ?>
                <div class="menu-item">
                    <?php if ($item['image_path']): ?>
                        <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
                    <?php endif; ?>
                    <div class="info">
                        <div class="name-row">
                            <span class="item-name"><?= e($item['name']) ?></span>
                            <span class="dot-leader"></span>
                            <span class="price"><?= formatMenuPrice((float) $item['price']) ?></span>
                        </div>
                        <?php if ($item['description']): ?><div class="desc"><?= e($item['description']) ?></div><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($grouped)): ?>
        <p style="color:var(--color-muted);"><?= e(t('menu_empty')) ?></p>
    <?php endif; ?>

    <?php $catIdx = 0; foreach ($grouped as $categoryName => $items): ?>
        <div class="menu-category" id="cat-<?= $catIdx++ ?>">
            <h2><?= e($categoryName) ?></h2>
            <?php foreach ($items as $item): ?>
                <div class="menu-item">
                    <?php if ($item['image_path']): ?>
                        <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
                    <?php endif; ?>
                    <div class="info">
                        <div class="name-row">
                            <span class="item-name"><?= e($item['name']) ?></span>
                            <span class="dot-leader"></span>
                            <span class="price"><?= formatMenuPrice((float) $item['price']) ?></span>
                        </div>
                        <?php if ($item['description']): ?>
                            <div class="desc"><?= e($item['description']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($restaurant['contact_phone'] || $restaurant['contact_address'] || $restaurant['contact_instagram'] || $restaurant['contact_whatsapp'] || $restaurant['contact_facebook'] || $restaurant['contact_x']): ?>
        <div class="contact-box">
            <h3><?= e(t('contact_title')) ?></h3>
            <?php if ($restaurant['contact_phone']): ?><p><?= menuIcon('phone') ?> <?= e($restaurant['contact_phone']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_address']): ?><p><?= menuIcon('pin') ?> <?= e($restaurant['contact_address']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_whatsapp']): ?><p><?= menuIcon('chat') ?> WhatsApp: <?= e($restaurant['contact_whatsapp']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_instagram']): ?><p><?= menuIcon('camera') ?> <?= e($restaurant['contact_instagram']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_facebook']): ?><p><?= menuIcon('facebook') ?> <?= e($restaurant['contact_facebook']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_x']): ?><p><?= menuIcon('x') ?> <?= e($restaurant['contact_x']) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include OM_ROOT . '/app/views/menu/_footer.php'; ?>
<?php if (count($catAnchors) > 1): ?>
<script>
(function () {
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-cat-link]'));
    var sections = links.map(function (l) { return document.getElementById(l.getAttribute('data-cat-link')); });
    var navWrap = document.querySelector('.category-nav');

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(link.getAttribute('data-cat-link'));
            if (!target) return;
            var offset = document.querySelector('.category-nav-wrap').offsetHeight + 8;
            window.scrollTo({ top: target.getBoundingClientRect().top + window.pageYOffset - offset, behavior: 'smooth' });
        });
    });

    function setActive() {
        var pos = window.pageYOffset + document.querySelector('.category-nav-wrap').offsetHeight + 20;
        var activeIdx = 0;
        sections.forEach(function (sec, i) {
            if (sec && sec.offsetTop <= pos) activeIdx = i;
        });
        links.forEach(function (l, i) {
            l.classList.toggle('current', i === activeIdx);
        });
        var activeLink = links[activeIdx];
        if (activeLink && navWrap) {
            var lr = activeLink.getBoundingClientRect();
            var nr = navWrap.getBoundingClientRect();
            if (lr.left < nr.left || lr.right > nr.right) {
                navWrap.scrollLeft += (lr.left - nr.left) - (nr.width - lr.width) / 2;
            }
        }
    }
    window.addEventListener('scroll', setActive, { passive: true });
    setActive();
})();
</script>
<?php endif; ?>
</body>
</html>
