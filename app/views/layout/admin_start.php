<?php include OM_ROOT . '/app/views/layout/header.php'; ?>
<div class="admin-layout">
    <div class="admin-sidebar">
        <a href="/admin" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Panel</a>
        <a href="/admin/products" class="<?= ($activeNav ?? '') === 'products' ? 'active' : '' ?>">Ürünler</a>
        <?php if ($restaurant['can_use_categories']): ?>
        <a href="/admin/categories" class="<?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>">Kategoriler</a>
        <?php endif; ?>
        <a href="/admin/contact" class="<?= ($activeNav ?? '') === 'contact' ? 'active' : '' ?>">İletişim</a>
        <?php if ($restaurant['can_upload_images']): ?>
        <a href="/admin/about" class="<?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>">Hakkımızda &amp; Galeri</a>
        <?php endif; ?>
        <?php if ($restaurant['can_view_analytics']): ?>
        <a href="/admin/analytics" class="<?= ($activeNav ?? '') === 'analytics' ? 'active' : '' ?>">Analitik</a>
        <?php endif; ?>
        <a href="/admin/qr" class="<?= ($activeNav ?? '') === 'qr' ? 'active' : '' ?>">QR Kodum</a>
        <a href="/admin/payment" class="<?= ($activeNav ?? '') === 'payment' ? 'active' : '' ?>">Ödeme</a>
        <a href="/admin/plan" class="<?= ($activeNav ?? '') === 'plan' ? 'active' : '' ?>">Planımı Değiştir</a>
        <a href="/menu/<?= e($restaurant['slug']) ?>" target="_blank">Menüyü Görüntüle ↗</a>
    </div>
    <div class="admin-content">
