<?php $activeNav = 'contact'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>İletişim Bilgileri</h2>
<p style="color:var(--color-muted)">Bu bilgiler müşterilerinizin gördüğü QR menü sayfasında gösterilir. Adresi yazmanız yeterli — yol tarifi ve harita otomatik olarak bu adrese göre çalışır, ayrıca bir koordinat girmenize gerek yok.</p>

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
            <input type="text" name="contact_address" value="<?= e($restaurant['contact_address'] ?? '') ?>" placeholder="Mahalle, cadde, no, ilçe/il">
        </div>
        <div class="form-group">
            <label>WhatsApp</label>
            <input type="text" name="contact_whatsapp" value="<?= e($restaurant['contact_whatsapp'] ?? '') ?>" placeholder="0555 555 55 55">
        </div>
        <div class="form-group">
            <label>Instagram</label>
            <input type="text" name="contact_instagram" value="<?= e($restaurant['contact_instagram'] ?? '') ?>" placeholder="@restoraniniz">
        </div>
        <div class="form-group">
            <label>Facebook</label>
            <input type="text" name="contact_facebook" value="<?= e($restaurant['contact_facebook'] ?? '') ?>" placeholder="facebook.com/restoraniniz">
        </div>
        <div class="form-group">
            <label>X (Twitter)</label>
            <input type="text" name="contact_x" value="<?= e($restaurant['contact_x'] ?? '') ?>" placeholder="@restoraniniz">
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
