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
<div class="page-header">
    <?php include OM_ROOT . '/app/views/menu/_nav.php'; ?>
    <div class="page-header-content">
        <h1><?= e(t('nav.about')) ?></h1>
    </div>
</div>

<div class="menu-public">
    <?php if (!empty($gallery)): ?>
        <div class="about-lead-photo">
            <img src="<?= e($gallery[0]['image_path']) ?>" alt="<?= e($restaurant['name']) ?>">
        </div>
    <?php endif; ?>

    <?php if (!empty($restaurant['about_text'])): ?>
        <div class="about-box">
            <?php if (!empty($restaurant['logo_path'])): ?>
                <div class="about-media">
                    <img src="<?= e($restaurant['logo_path']) ?>" alt="<?= e($restaurant['name']) ?>">
                </div>
            <?php endif; ?>
            <div class="about-copy">
                <h3><?= e($restaurant['name']) ?></h3>
                <p><?= nl2br(e($restaurant['about_text'])) ?></p>
            </div>
        </div>
    <?php else: ?>
        <p style="color:var(--color-muted);"><?= e(t('about_empty')) ?></p>
    <?php endif; ?>

    <?php $restGallery = count($gallery) > 1 ? array_slice($gallery, 1) : []; ?>
    <?php if (!empty($restGallery)): ?>
        <div class="photo-grid">
            <?php foreach ($restGallery as $photo): ?>
                <div class="photo-grid-item"><img src="<?= e($photo['image_path']) ?>" alt=""></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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
</body>
</html>
