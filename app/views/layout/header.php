<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'QR Menü') ?></title>
    <link rel="icon" href="/assets/favicon.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<div class="navbar">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;">
        <a href="/" class="brand">QRMenü</a>
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
