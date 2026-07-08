<?php $activeNav = 'dashboard'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<div class="toolbar">
    <h2>Merhaba, <?= e($restaurant['name']) ?></h2>
    <form method="post" action="/admin/toggle" onsubmit="return confirm('<?= $restaurant['is_open'] ? 'Menünüzü kapatmak istediğinize emin misiniz?' : 'Menünüzü tekrar açmak istiyor musunuz?' ?><?= csrfField() ?>');">
        <button type="submit" class="btn <?= $restaurant['is_open'] ? 'danger' : '' ?>">
            <?= $restaurant['is_open'] ? 'Menüyü Kapat' : 'Menüyü Aç' ?>
        </button>
    </form>
</div>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

<?php if ($restaurant['subscription_status'] === 'trial'): ?>
    <div class="alert info">Ücretsiz deneme sürümündesiniz — <strong><?= (int) $trial['days_left'] ?> gün</strong> kaldı (bitiş: <?= (new DateTime($restaurant['trial_ends_at']))->format('d.m.Y') ?>).</div>
<?php elseif ($restaurant['subscription_status'] === 'expired'): ?>
    <div class="alert error">Deneme/abonelik süreniz doldu (<?= (new DateTime($restaurant['trial_ends_at']))->format('d.m.Y') ?>). Menünüz müşterilere kapalı görünüyor. Devam etmek için bizimle iletişime geçin.</div>
<?php elseif ($restaurant['subscription_status'] === 'canceled'): ?>
    <div class="alert error">Üyeliğiniz iptal edilmiş durumda. Menünüz müşterilere görünmüyor.</div>
<?php endif; ?>

<?php if (!$restaurant['iyzico_card_token'] && !in_array($restaurant['subscription_status'], ['canceled', 'expired'], true)): ?>
    <div class="alert info">Henüz kayıtlı bir kartınız yok. Deneme süreniz bitince menünüzün kapanmaması için <a href="/admin/payment">kartınızı ekleyin</a>.</div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-tile">
        <div class="stat-tile-label">Plan</div>
        <div class="stat-tile-value"><?= e($restaurant['plan_name']) ?></div>
        <span class="badge <?= e($restaurant['subscription_status']) ?>"><?= e($restaurant['subscription_status']) ?></span>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">Menü Durumu</div>
        <div class="stat-tile-value"><?= $restaurant['is_open'] ? 'Açık' : 'Kapalı' ?></div>
        <span class="badge <?= $restaurant['is_open'] ? 'open' : 'closed' ?>"><?= $restaurant['is_open'] ? 'Yayında' : 'Gizli' ?></span>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">Ürün Sayısı</div>
        <div class="stat-tile-value"><?= (int) $productCount ?><?= $restaurant['max_products'] ? ' / ' . (int) $restaurant['max_products'] : '' ?></div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label"><?= $restaurant['subscription_status'] === 'trial' ? 'Deneme Bitiş Tarihi' : 'Üyelik Son Günü' ?></div>
        <div class="stat-tile-value"><?= (new DateTime($restaurant['trial_ends_at']))->format('d.m.Y') ?></div>
    </div>
</div>

<div class="card">
    <p style="margin-top:0;"><strong>Menü linkiniz:</strong> <a href="<?= e($menuUrl) ?>" target="_blank"><?= e($menuUrl) ?></a></p>
    <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <a href="/admin/products/new" class="btn">+ Yeni Ürün Ekle</a>
        <a href="/admin/qr" class="btn secondary">QR Kodumu Gör</a>
        <a href="/admin/contact" class="btn secondary">İletişim Bilgilerini Düzenle</a>
    </div>
</div>

<?php if (!in_array($restaurant['subscription_status'], ['canceled', 'expired'], true)): ?>
<div class="card danger-zone">
    <h3>Üyeliği İptal Et</h3>
    <p>Üyeliğinizi iptal ederseniz menünüz anında müşterilere kapanır ve panelinize erişiminiz kısıtlanır. Ürünleriniz ve verileriniz silinmez, dilediğiniz zaman bizimle iletişime geçip tekrar aktif edebilirsiniz.</p>
    <form method="post" action="/admin/cancel-membership" onsubmit="return confirm('Üyeliğinizi iptal etmek istediğinize emin misiniz? Menünüz hemen kapanacak.');"><?= csrfField() ?>
        <button type="submit" class="btn danger">Üyeliği İptal Et</button>
    </form>
</div>
<?php endif; ?>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
