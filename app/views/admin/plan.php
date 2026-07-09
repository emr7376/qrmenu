<?php $activeNav = 'plan'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>Planımı Değiştir</h2>
<p style="color:var(--color-muted)">Plan değiştirmek ürün, kategori, görsel gibi mevcut verilerinizi silmez — sadece hangi özelliklerin panelinizde açık olduğunu değiştirir.</p>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php if (in_array($restaurant['subscription_status'], ['canceled', 'expired'], true)): ?>
    <div class="alert info">Üyeliğiniz şu an <?= $restaurant['subscription_status'] === 'canceled' ? 'iptal edilmiş' : 'sona ermiş' ?> durumda. Aşağıdan bir plan seçerseniz üyeliğiniz yeniden aktifleşir ve menünüz tekrar açılır.</div>
<?php endif; ?>

<div style="display:flex;gap:16px;flex-wrap:wrap;">
    <?php foreach ($plans as $plan): ?>
        <?php $isCurrent = (int) $plan['id'] === (int) $restaurant['plan_id'] && !in_array($restaurant['subscription_status'], ['canceled', 'expired'], true); ?>
        <div class="card" style="max-width:260px;flex:1;<?= $isCurrent ? 'border-color:var(--color-accent);' : '' ?>">
            <h3><?= e($plan['name']) ?><?= $isCurrent ? ' (Mevcut Plan)' : '' ?></h3>
            <p style="font-size:1.4rem;margin:4px 0;"><?= number_format((float) $plan['price_monthly'], 0, ',', '.') ?>₺<span style="font-size:0.85rem;color:var(--color-muted);">/ay</span></p>
            <ul style="color:var(--color-muted);font-size:0.9rem;padding-left:18px;margin-bottom:16px;">
                <li>Ürün sayısı: sınırsız</li>
                <li><?= $plan['can_use_categories'] ? '✓' : '—' ?> Kategoriler</li>
                <li><?= $plan['can_upload_images'] ? '✓' : '—' ?> Görsel/galeri yükleme</li>
                <li><?= $plan['can_feature_products'] ? '✓' : '—' ?> Öne çıkan ürün</li>
                <li><?= $plan['can_customize_theme'] ? '✓' : '—' ?> Tema/QR özelleştirme</li>
                <li><?= $plan['can_view_analytics'] ? '✓' : '—' ?> Analitik</li>
            </ul>
            <?php if ($isCurrent): ?>
                <button type="button" class="btn" disabled>Kullanımdaki Plan</button>
            <?php else: ?>
                <form method="post" action="/admin/plan" onsubmit="return confirm('Plan değişikliği hemen uygulanacak. Onaylıyor musunuz?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                    <button type="submit" class="btn"><?= (float) $plan['price_monthly'] > (float) ($restaurant['price_monthly'] ?? 0) ? 'Yükselt' : 'Bu Plana Geç' ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
