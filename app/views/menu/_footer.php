<footer class="site-footer">
    <div class="site-footer-inner">
        <span class="site-footer-brand"><?= e($restaurant['name']) ?></span>
        <nav class="site-footer-links">
            <a href="/menu/<?= e($restaurant['slug']) ?>"><?= e(t('nav.home')) ?></a>
            <?php if (!empty($restaurant['about_text'])): ?>
                <a href="/menu/<?= e($restaurant['slug']) ?>/hakkimizda"><?= e(t('nav.about')) ?></a>
            <?php endif; ?>
            <a href="/menu/<?= e($restaurant['slug']) ?>/menu"><?= e(t('nav.menu')) ?></a>
            <a href="/menu/<?= e($restaurant['slug']) ?>/konum"><?= e(t('nav.contact')) ?></a>
        </nav>
        <span class="site-footer-credit"><?= (int) date('Y') ?> · <a href="/">QRMenü</a></span>
    </div>
</footer>
