<?php
    $metaDescription = $metaDescription ?? 'Restoranınız için QR kodlu dijital menü. Ürün ekleyin, fiyatlandırın, QR kodunuzu masalarınıza koyun - menünüz her güncellemede anında yenilenir. 7 gün ücretsiz deneyin.';
    $canonical = canonicalUrl($_SERVER['REQUEST_URI'] ?? '/');
    $pageTitle = e($title ?? 'QR Menü');
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="ZvfQm4hz3Kd1DCgNOApmNq1Z8hT1t3PrE97plyr_wd4" />
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <link rel="icon" href="/assets/favicon.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (!empty($structuredData)): ?>
    <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<div class="navbar">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:baseline;gap:14px;">
            <a href="/" class="brand">QRMenü</a>
            <?php if (!empty($showStaffLink)): ?>
                <a href="/superadmin/login" style="color:var(--color-muted);font-size:0.78rem;">Personel Girişi</a>
            <?php endif; ?>
        </div>
        <nav>
            <?php if (Auth::check()): ?>
                <a href="/admin">Panelim</a>
                <a href="/logout">Çıkış</a>
            <?php else: ?>
                <a href="/login">Restoran Girişi</a>
                <a href="/login?tab=signup" class="btn small button">Üye Ol</a>
            <?php endif; ?>
        </nav>
    </div>
</div>
