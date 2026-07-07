<?php $activeNav = 'qr'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>QR Kodum</h2>
<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card qr-box">
    <p>Bu QR kodu masalarınıza, vitrininize veya kartvizitlerinize koyabilirsiniz. Okutulduğunda müşteri doğrudan menünüze ulaşır.</p>
    <div id="qrcode"></div>
    <p><a href="<?= e($menuUrl) ?>" target="_blank"><?= e($menuUrl) ?></a></p>
    <button class="btn" onclick="downloadQr()">PNG olarak indir</button>
</div>

<?php if ($restaurant['can_customize_theme']): ?>
<div class="card" style="max-width:420px;">
    <h3>QR Görünümünü Özelleştir</h3>
    <form method="post" action="/admin/qr" enctype="multipart/form-data">
        <div class="form-group">
            <label>Renk</label>
            <input type="color" name="qr_color" value="<?= e($restaurant['qr_color'] ?? '#24201d') ?>" style="width:70px;height:40px;padding:2px;">
        </div>
        <div class="form-group">
            <label>Logo (ortada görünür, küçük ve sade bir logo önerilir)</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
            <?php if (!empty($restaurant['qr_logo_path'])): ?>
                <div style="margin-top:8px;"><img src="<?= e($restaurant['qr_logo_path']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</div>

<div class="card" style="max-width:420px;">
    <h3>Menü Sitenizin Rengi</h3>
    <p style="color:var(--color-muted);font-size:0.88rem;margin-top:-8px;">Müşterilerinizin gördüğü menü sitesindeki vurgu rengini (fiyatlar, rozetler, ikonlar) markanıza göre değiştirin.</p>
    <form method="post" action="/admin/theme">
        <div class="form-group">
            <label>Vurgu Rengi</label>
            <input type="color" name="theme_color" value="<?= e($restaurant['theme_color'] ?? '#9c8452') ?>" style="width:70px;height:40px;padding:2px;">
        </div>
        <button type="submit" class="btn">Kaydet</button>
        <?php if (!empty($restaurant['theme_color'])): ?>
            <a href="<?= e($menuUrl) ?>" target="_blank" class="btn secondary small" style="margin-left:8px;">Sitede Gör</a>
        <?php endif; ?>
    </form>
</div>
<?php else: ?>
<div class="card" style="max-width:420px;">
    <p style="color:var(--color-muted)">QR rengini, logonuzu ve menü sitenizin vurgu rengini özelleştirmek için planınızı yükseltin.</p>
</div>
<?php endif; ?>

<script src="/assets/js/qrcode.min.js"></script>
<script>
    const qr = new QRCode(document.getElementById("qrcode"), {
        text: <?= json_encode($menuUrl) ?>,
        width: 240,
        height: 240,
        colorDark: <?= json_encode($restaurant['qr_color'] ?? '#24201d') ?>,
        colorLight: "#ffffff",
    });

    <?php if (!empty($restaurant['qr_logo_path'])): ?>
    setTimeout(function () {
        const canvas = document.querySelector('#qrcode canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const logo = new Image();
        logo.crossOrigin = 'anonymous';
        logo.onload = function () {
            const size = canvas.width * 0.2;
            const x = (canvas.width - size) / 2;
            const y = (canvas.height - size) / 2;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(x - 4, y - 4, size + 8, size + 8);
            ctx.drawImage(logo, x, y, size, size);
        };
        logo.src = <?= json_encode($restaurant['qr_logo_path']) ?>;
    }, 150);
    <?php endif; ?>

    function downloadQr() {
        const canvas = document.querySelector('#qrcode canvas');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'qr-menu.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
</script>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
