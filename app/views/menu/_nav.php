<?php $currentPath = strtok($_SERVER['REQUEST_URI'], '?'); ?>
<nav class="menu-nav">
    <span class="menu-nav-brand"><?= e($restaurant['name']) ?></span>
    <div class="menu-nav-links">
        <a href="/menu/<?= e($restaurant['slug']) ?>" class="<?= $activePage === 'home' ? 'current' : '' ?>"><?= e(t('nav.home')) ?></a>
        <?php if (!empty($restaurant['about_text'])): ?>
            <a href="/menu/<?= e($restaurant['slug']) ?>/hakkimizda" class="<?= $activePage === 'about' ? 'current' : '' ?>"><?= e(t('nav.about')) ?></a>
        <?php endif; ?>
        <a href="/menu/<?= e($restaurant['slug']) ?>/menu" class="<?= $activePage === 'menu' ? 'current' : '' ?>"><?= e(t('nav.menu')) ?></a>
        <a href="/menu/<?= e($restaurant['slug']) ?>/konum" class="<?= $activePage === 'location' ? 'current' : '' ?>"><?= e(t('nav.contact')) ?></a>
        <span class="lang-switch">
            <a href="<?= e($currentPath) ?>?lang=tr" class="<?= menuLang() === 'tr' ? 'current' : '' ?>">TR</a>
            <span class="lang-sep">/</span>
            <a href="<?= e($currentPath) ?>?lang=en" class="<?= menuLang() === 'en' ? 'current' : '' ?>">EN</a>
        </span>
    </div>
</nav>
