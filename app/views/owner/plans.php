<?php $activeNav = 'plans'; include OM_ROOT . '/app/views/owner/_start.php'; ?>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

<div class="owner-section-title">
    <h2>Planlar</h2>
    <span>Fiyat ve özellik matrisini buradan yönet</span>
</div>

<div class="plans">
    <?php foreach ($plans as $plan): ?>
        <form method="post" action="/superadmin/plans/<?= (int) $plan['id'] ?>" class="plan-card plan-edit-card">
            <div class="form-group">
                <label>Plan Adı</label>
                <input type="text" name="name" value="<?= e($plan['name']) ?>">
            </div>
            <div class="form-group">
                <label>Aylık Fiyat (₺)</label>
                <input type="text" name="price_monthly" value="<?= e((string) $plan['price_monthly']) ?>">
            </div>
            <div class="form-group">
                <label>Ürün Limiti (boş = sınırsız)</label>
                <input type="text" name="max_products" value="<?= e($plan['max_products'] !== null ? (string) $plan['max_products'] : '') ?>">
            </div>
            <div class="flags">
                <label><input type="checkbox" name="can_upload_images" <?= $plan['can_upload_images'] ? 'checked' : '' ?>> Görsel yükleme</label>
                <label><input type="checkbox" name="can_use_categories" <?= $plan['can_use_categories'] ? 'checked' : '' ?>> Kategoriler</label>
                <label><input type="checkbox" name="can_customize_theme" <?= $plan['can_customize_theme'] ? 'checked' : '' ?>> Tema/QR özelleştirme</label>
                <label><input type="checkbox" name="can_feature_products" <?= $plan['can_feature_products'] ? 'checked' : '' ?>> Öne çıkan ürün</label>
                <label><input type="checkbox" name="can_view_analytics" <?= $plan['can_view_analytics'] ? 'checked' : '' ?>> Analitik</label>
            </div>
            <button type="submit" class="btn" style="width:100%;">Kaydet</button>
        </form>
    <?php endforeach; ?>
</div>

<?php include OM_ROOT . '/app/views/owner/_end.php'; ?>
