<?php $activeNav = 'contact'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>İletişim Bilgileri</h2>
<p style="color:var(--color-muted)">Bu bilgiler müşterilerinizin gördüğü QR menü sayfasında gösterilir.</p>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card" style="max-width:520px;">
    <form method="post" action="/admin/contact">
        <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="contact_phone" value="<?= e($restaurant['contact_phone'] ?? '') ?>" placeholder="0555 555 55 55">
        </div>
        <div class="form-group">
            <label>Adres</label>
            <input type="text" name="contact_address" value="<?= e($restaurant['contact_address'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Instagram</label>
            <input type="text" name="contact_instagram" value="<?= e($restaurant['contact_instagram'] ?? '') ?>" placeholder="@restoraniniz">
        </div>
        <div class="form-group">
            <label>WhatsApp</label>
            <input type="text" name="contact_whatsapp" value="<?= e($restaurant['contact_whatsapp'] ?? '') ?>" placeholder="0555 555 55 55">
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</div>

<div class="card" style="max-width:520px;">
    <h3>Konum</h3>
    <p style="color:var(--color-muted);font-size:0.9rem;">Müşterileriniz "Konum" sayfasında buradan size olan mesafeyi ve yol tarifini görebilsin. Google Maps'te restoranınızı bulup üzerine sağ tıklayıp çıkan koordinatlara tıklayarak kopyalayabilirsiniz.</p>
    <form method="post" action="/admin/contact">
        <div style="display:flex;gap:12px;">
            <div class="form-group" style="flex:1;">
                <label>Enlem (Latitude)</label>
                <input type="text" name="latitude" value="<?= e($restaurant['latitude'] ?? '') ?>" placeholder="41.0082">
            </div>
            <div class="form-group" style="flex:1;">
                <label>Boylam (Longitude)</label>
                <input type="text" name="longitude" value="<?= e($restaurant['longitude'] ?? '') ?>" placeholder="28.9784">
            </div>
        </div>
        <input type="hidden" name="contact_phone" value="<?= e($restaurant['contact_phone'] ?? '') ?>">
        <input type="hidden" name="contact_address" value="<?= e($restaurant['contact_address'] ?? '') ?>">
        <input type="hidden" name="contact_instagram" value="<?= e($restaurant['contact_instagram'] ?? '') ?>">
        <input type="hidden" name="contact_whatsapp" value="<?= e($restaurant['contact_whatsapp'] ?? '') ?>">
        <button type="submit" class="btn">Konumu Kaydet</button>
    </form>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
