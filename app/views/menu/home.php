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
<?php if ($restaurant['can_upload_images']): ?>
<div class="menu-hero">
    <?php include OM_ROOT . '/app/views/menu/_nav.php'; ?>
    <?php if (!empty($gallery)): ?>
        <div id="galleryCarousel" class="gallery-layer">
            <?php foreach ($gallery as $i => $photo): ?>
                <img src="<?= e($photo['image_path']) ?>" alt="" class="gallery-slide <?= $i === 0 ? 'active' : '' ?>">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="hero-content">
        <h1><?= e($restaurant['name']) ?></h1>
        <?php if ($restaurant['contact_address'] || $restaurant['contact_phone']): ?>
            <div class="hero-meta">
                <?php if ($restaurant['contact_address']): ?><span><?= menuIcon('pin') ?> <?= e($restaurant['contact_address']) ?></span><?php endif; ?>
                <?php if ($restaurant['contact_phone']): ?><span><?= menuIcon('phone') ?> <?= e($restaurant['contact_phone']) ?></span><?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="hero-actions">
            <a href="/menu/<?= e($restaurant['slug']) ?>/menu" class="btn"><?= e(t('cta.view_menu')) ?></a>
            <?php if (!empty($restaurant['about_text'])): ?>
                <a href="/menu/<?= e($restaurant['slug']) ?>/hakkimizda" class="btn secondary-on-dark"><?= e(t('cta.about')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="simple-header">
    <?php include OM_ROOT . '/app/views/menu/_nav.php'; ?>
    <div class="simple-header-content">
        <h1><?= e($restaurant['name']) ?></h1>
        <?php if ($restaurant['contact_address'] || $restaurant['contact_phone']): ?>
            <div class="simple-header-meta">
                <?php if ($restaurant['contact_address']): ?><span><?= menuIcon('pin') ?> <?= e($restaurant['contact_address']) ?></span><?php endif; ?>
                <?php if ($restaurant['contact_phone']): ?><span><?= menuIcon('phone') ?> <?= e($restaurant['contact_phone']) ?></span><?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="hero-actions">
            <a href="/menu/<?= e($restaurant['slug']) ?>/menu" class="btn"><?= e(t('cta.view_menu')) ?></a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="menu-public">
    <?php if (!empty($featured)): ?>
        <div class="menu-category featured-category">
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
            <a href="/menu/<?= e($restaurant['slug']) ?>/menu" class="link-more"><?= e(t('see_full_menu')) ?></a>
        </div>
    <?php endif; ?>

    <?php if ($restaurant['contact_phone'] || $restaurant['contact_address'] || $restaurant['contact_instagram'] || $restaurant['contact_whatsapp']): ?>
        <div class="contact-box">
            <h3><?= e(t('contact_title')) ?></h3>
            <?php if ($restaurant['contact_phone']): ?><p><?= menuIcon('phone') ?> <?= e($restaurant['contact_phone']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_address']): ?><p><?= menuIcon('pin') ?> <?= e($restaurant['contact_address']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_whatsapp']): ?><p><?= menuIcon('chat') ?> WhatsApp: <?= e($restaurant['contact_whatsapp']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_instagram']): ?><p><?= menuIcon('camera') ?> <?= e($restaurant['contact_instagram']) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include OM_ROOT . '/app/views/menu/_footer.php'; ?>

<?php if (count($gallery) > 1): ?>
<script>
    (function () {
        var slides = document.querySelectorAll('#galleryCarousel .gallery-slide');
        var current = 0;
        setInterval(function () {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 3500);
    })();
</script>
<?php endif; ?>
</body>
</html>
