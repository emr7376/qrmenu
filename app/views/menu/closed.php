<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <link rel="icon" href="<?= !empty($restaurant['logo_path']) ? e($restaurant['logo_path']) : '/assets/favicon.svg' ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="site-public">
<div class="closed-screen">
    <h1><?= menuIcon('lock') ?> <?= e($restaurant['name']) ?></h1>
    <p><?= e(t('closed_body')) ?></p>
</div>
</body>
</html>
