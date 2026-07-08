<?php $activeNav = 'payment'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>Ödeme</h2>
<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php if ($needsBillingInfo): ?>
<div class="card" style="max-width:480px;">
    <h3>Fatura Bilgileri</h3>
    <p style="color:var(--color-muted);font-size:0.88rem;margin-top:-8px;">Kart ekleyebilmek için önce fatura bilgilerinizi tamamlamanız gerekiyor.</p>
    <form method="post" action="/admin/billing-info"><?= csrfField() ?>
        <div class="form-group">
            <label>TC Kimlik No / Vergi No</label>
            <input type="text" name="billing_identity_number" value="<?= e($restaurant['billing_identity_number'] ?? '') ?>" maxlength="11" required>
        </div>
        <div class="form-group">
            <label>Şehir</label>
            <input type="text" name="billing_city" value="<?= e($restaurant['billing_city'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="contact_phone" value="<?= e($restaurant['contact_phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Adres</label>
            <input type="text" name="contact_address" value="<?= e($restaurant['contact_address'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</div>
<?php else: ?>
<div class="card" style="max-width:480px;">
    <?php if ($restaurant['iyzico_card_token']): ?>
        <h3>Kayıtlı Kartınız Var</h3>
        <p>Her ay otomatik olarak <strong><?= number_format((float) $restaurant['price_monthly'], 0, ',', '.') ?>₺</strong> tahsil edilecek.
            <?php if ($restaurant['next_billing_at']): ?>
                Sıradaki tahsilat: <strong><?= (new DateTime($restaurant['next_billing_at']))->format('d.m.Y') ?></strong>.
            <?php endif; ?>
        </p>
        <form method="post" action="/admin/payment/start"><?= csrfField() ?>
            <button type="submit" class="btn secondary">Kartımı Değiştir</button>
        </form>
    <?php else: ?>
        <h3>Kart Ekle</h3>
        <p style="color:var(--color-muted);font-size:0.88rem;">Kartınızı iyzico'nun güvenli ödeme sayfasında kaydedeceksiniz — kart bilgileriniz bizim sunucumuza hiç ulaşmaz. Kart kaydedildikten sonra üyeliğiniz aktifleşir ve her ay otomatik tahsilat yapılır.</p>
        <form method="post" action="/admin/payment/start"><?= csrfField() ?>
            <button type="submit" class="btn">Kart Ekle ve Üyeliği Aktifleştir</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
