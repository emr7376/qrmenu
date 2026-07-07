<?php $bodyClass = 'site-public'; include OM_ROOT . '/app/views/layout/header.php'; ?>
<div class="container">
    <div class="auth-box card" style="max-width:420px;text-align:center;">
        <h2>Giriş Kodu</h2>
        <p style="color:var(--color-muted);margin-top:-8px;">E-posta adresinize gönderdiğimiz 6 haneli kodu girin.</p>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($info): ?><div class="alert info"><?= e($info) ?></div><?php endif; ?>
        <form method="post" action="/login/verify">
            <div class="form-group">
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus required
                       style="text-align:center;font-size:1.6rem;letter-spacing:0.5em;font-family:var(--font-serif);">
            </div>
            <button type="submit" class="btn" style="width:100%;">Doğrula ve Giriş Yap</button>
        </form>
        <form method="post" action="/login/verify/resend" style="margin-top:14px;">
            <button type="submit" class="btn secondary small">Kodu Tekrar Gönder</button>
        </form>
        <p style="margin-top:18px;"><a href="/login">← Girişe dön</a></p>
    </div>
</div>
<?php include OM_ROOT . '/app/views/layout/footer.php'; ?>
