<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" href="/assets/favicon.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="owner-topbar">
    <div class="container owner-topbar-inner">
        <a href="/" class="owner-brand">QRMenü<span class="owner-badge">Yönetici</span></a>
        <nav class="owner-nav">
            <a href="/superadmin" class="<?= ($activeNav ?? '') === 'restaurants' ? 'current' : '' ?>">Restoranlar</a>
            <a href="/superadmin/plans" class="<?= ($activeNav ?? '') === 'plans' ? 'current' : '' ?>">Planlar</a>
            <a href="/superadmin/database" class="<?= ($activeNav ?? '') === 'database' ? 'current' : '' ?>">Veritabanı</a>
            <a href="/superadmin/logout" class="owner-logout">Çıkış</a>
        </nav>
    </div>
</div>
<div class="container owner-content">
