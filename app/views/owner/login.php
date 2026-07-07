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
<div class="container" style="padding-top:60px;">
    <div style="text-align:center;margin-bottom:24px;">
        <a href="/" class="owner-brand" style="text-decoration:none;">QRMenü<span class="owner-badge">Yönetici</span></a>
    </div>
    <div class="auth-box card">
        <h2>Yönetici Girişi</h2>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="/superadmin/login">
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width:100%;">Giriş Yap</button>
        </form>
    </div>
</div>
</body>
</html>
