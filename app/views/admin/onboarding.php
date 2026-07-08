<?php include OM_ROOT . '/app/views/layout/header.php'; ?>

<div class="container" style="max-width:640px;padding-top:32px;padding-bottom:48px;">

    <div style="display:flex;gap:8px;margin-bottom:28px;">
        <div style="flex:1;height:6px;border-radius:3px;background:var(--color-primary);"></div>
        <div style="flex:1;height:6px;border-radius:3px;background:<?= $step === 'done' ? 'var(--color-primary)' : 'var(--color-border, #e5e0d8)' ?>;"></div>
    </div>

    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($step === 'product'): ?>
        <h1 style="margin-bottom:4px;">Hoş geldin, <?= e($restaurant['name']) ?> 👋</h1>
        <p style="color:var(--color-muted);margin-bottom:28px;">
            QRMenü'de her şey <strong>ürünlerden</strong> oluşur. Restoranınızın menüsünde müşterilerinizin göreceği
            her satır (örn. "Mercimek Çorbası - 90₺") bir üründür. Şimdi ilk ürününüzü ekleyelim ki menünüzün
            gerçekte nasıl görüneceğini hemen görebilesiniz. Merak etme, istediğin zaman panelden ürün ekleyip
            silebilir, düzenleyebilirsin — burada sadece ilk adımı atıyoruz.
        </p>

        <div class="card" style="max-width:420px;">
            <h3>İlk Ürününü Ekle</h3>
            <form method="post" action="/admin/onboarding/product"><?= csrfField() ?>
                <div class="form-group">
                    <label>Ürün Adı</label>
                    <input type="text" name="name" placeholder="Örn. Mercimek Çorbası" required autofocus>
                </div>
                <div class="form-group">
                    <label>Fiyat (₺)</label>
                    <input type="text" name="price" placeholder="Örn. 90">
                </div>
                <button type="submit" class="btn">Ürünü Ekle ve Devam Et</button>
            </form>
        </div>

        <p style="margin-top:20px;">
            <a href="/admin/onboarding/finish" style="color:var(--color-muted);font-size:0.9rem;">Şimdi değil, sonra kendim eklerim →</a>
        </p>

    <?php else: ?>
        <h1 style="margin-bottom:4px;">Menün hazır 🎉</h1>
        <p style="color:var(--color-muted);margin-bottom:28px;">
            İşte bu — restoranının menüsü artık gerçekten yayında. Aşağıdaki QR kodu telefonunla okutarak
            müşterilerinin göreceği ekranı hemen kendi gözlerinle görebilirsin. Bu kodu indirip masalarına,
            vitrinine ya da kartvizitlerine koyabilirsin; menünde bir şey değiştirdiğinde kod aynı kalır,
            içerik anında güncellenir — yeniden bastırmana gerek kalmaz.
        </p>

        <div class="card qr-box" style="max-width:340px;">
            <div id="qrcode"></div>
            <p><a href="<?= e($menuUrl) ?>" target="_blank"><?= e($menuUrl) ?></a></p>
            <button class="btn" onclick="downloadQr()">PNG olarak indir</button>
        </div>

        <div class="card" style="max-width:420px;margin-top:16px;">
            <h3>Sırada ne var?</h3>
            <p style="color:var(--color-muted);font-size:0.9rem;">
                Panelden daha fazla ürün ekleyebilir, iletişim bilgilerini girebilir ve (planına göre) görsel/kategori
                ekleyebilirsin. Sol menüdeki her bölüm ne işe yaradığını üstünde yazıyor, gezinerek keşfedebilirsin.
            </p>
        </div>

        <p style="margin-top:24px;">
            <a href="/admin/onboarding/finish" class="btn">Panele Git</a>
        </p>

        <script src="/assets/js/qrcode.min.js"></script>
        <script>
            const qr = new QRCode(document.getElementById("qrcode"), {
                text: <?= json_encode($menuUrl) ?>,
                width: 220,
                height: 220,
                colorDark: <?= json_encode($restaurant['qr_color'] ?? '#24201d') ?>,
                colorLight: "#ffffff",
            });
            function downloadQr() {
                const canvas = document.querySelector('#qrcode canvas');
                if (!canvas) return;
                const link = document.createElement('a');
                link.download = 'qr-menu.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }
        </script>
    <?php endif; ?>

</div>

<?php include OM_ROOT . '/app/views/layout/footer.php'; ?>
