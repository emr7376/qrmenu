<?php $activeNav = 'restaurants'; include OM_ROOT . '/app/views/owner/_start.php'; ?>

<div class="owner-section-title">
    <h2>Yeni Restoran Ekle</h2>
</div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="card" style="max-width:460px;">
    <form method="post" action="/superadmin/restaurants/new"><?= csrfField() ?>
        <div class="form-group">
            <label>Restoran Adı</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>E-posta (restoran bununla giriş yapacak)</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Şifre (boş bırakırsan otomatik oluşturulur)</label>
            <input type="text" name="password">
        </div>
        <div class="form-group">
            <label>Plan</label>
            <select name="plan_id">
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= (int) $plan['id'] ?>"><?= e($plan['name']) ?> — <?= number_format((float) $plan['price_monthly'], 0, ',', '.') ?>₺/ay</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn" style="width:100%;">Restoranı Oluştur</button>
    </form>
</div>

<?php include OM_ROOT . '/app/views/owner/_end.php'; ?>
