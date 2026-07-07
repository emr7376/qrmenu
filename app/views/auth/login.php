<?php $bodyClass = 'site-public'; $showStaffLink = true; include OM_ROOT . '/app/views/layout/header.php'; ?>
<div class="container">
    <div class="auth-box card" style="max-width:460px;">
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <button type="button" id="tab-login" class="btn small" onclick="pawShowTab('login')" style="flex:1;">Giriş Yap</button>
            <button type="button" id="tab-signup" class="btn small" onclick="pawShowTab('signup')" style="flex:1;">Üye Ol</button>
        </div>

        <div id="pane-login">
            <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="/login">
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

        <div id="pane-signup" style="display:none;">
            <p style="color:#666;margin-top:0;"><?= (int) OM_TRIAL_DAYS ?> gün ücretsiz deneme ile hemen başlayın.</p>
            <?php if ($signupError): ?><div class="alert error"><?= e($signupError) ?></div><?php endif; ?>
            <form method="post" action="/signup">
                <div class="form-group">
                    <label>Restoran Adı</label>
                    <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>E-posta (giriş için kullanacaksınız)</label>
                    <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Şifre (en az 6 karakter)</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Plan</label>
                    <div class="plan-pick-row">
                        <?php foreach ($plans as $i => $plan): ?>
                            <?php $isSelected = $selectedPlanId ? $selectedPlanId == $plan['id'] : $i === 1; ?>
                            <label class="plan-pick <?= $isSelected ? 'checked' : '' ?>">
                                <input type="radio" name="plan_id" value="<?= (int) $plan['id'] ?>" <?= $isSelected ? 'checked' : '' ?> onchange="pawSelectPlan(this)">
                                <span class="plan-pick-name"><?= e($plan['name']) ?></span>
                                <span class="plan-pick-price"><?= number_format((float) $plan['price_monthly'], 0, ',', '.') ?>₺/ay</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="plan-features-box" class="plan-features-box"></div>
                <button type="submit" class="btn" style="width:100%;">Ücretsiz Denemeyi Başlat</button>
            </form>
        </div>
    </div>
</div>
<script>
function pawShowTab(name) {
    document.getElementById('pane-login').style.display = name === 'login' ? '' : 'none';
    document.getElementById('pane-signup').style.display = name === 'signup' ? '' : 'none';
}

var PAW_PLAN_FEATURES = <?= json_encode(array_column(array_map(function ($plan) {
    $features = [];
    $features[] = $plan['max_products'] ? ((int) $plan['max_products'] . ' ürüne kadar') : 'Sınırsız ürün';
    $features[] = $plan['can_use_categories'] ? 'Kategori düzenleme' : null;
    $features[] = $plan['can_upload_images'] ? 'Ürün ve galeri fotoğrafı yükleme' : null;
    $features[] = $plan['can_upload_images'] ? 'Hakkımızda yazısı ve banner\'lı anasayfa' : null;
    $features[] = $plan['can_feature_products'] ? 'Ürünleri öne çıkarma' : null;
    $features[] = $plan['can_view_analytics'] ? 'Ziyaret analitiği' : null;
    $features[] = $plan['can_customize_theme'] ? 'QR kod tema/logo özelleştirme' : null;
    $features[] = 'Kendi QR kodunuz';
    $features[] = 'İletişim bölümü';
    return ['id' => (int) $plan['id'], 'features' => array_values(array_filter($features))];
}, $plans), 'features', 'id')) ?>;

function pawShowPlanFeatures(planId) {
    var features = PAW_PLAN_FEATURES[planId] || [];
    var box = document.getElementById('plan-features-box');
    box.innerHTML = '<ul>' + features.map(function (f) { return '<li>✓ ' + f + '</li>'; }).join('') + '</ul>';
}

function pawSelectPlan(radio) {
    document.querySelectorAll('.plan-pick').forEach(function (el) { el.classList.remove('checked'); });
    radio.closest('.plan-pick').classList.add('checked');
    pawShowPlanFeatures(radio.value);
}

var pawCheckedPlan = document.querySelector('.plan-pick input:checked');
if (pawCheckedPlan) pawShowPlanFeatures(pawCheckedPlan.value);

<?php if ($signupError || !empty($old) || $defaultTab === 'signup'): ?>
pawShowTab('signup');
<?php endif; ?>
</script>
<?php include OM_ROOT . '/app/views/layout/footer.php'; ?>
