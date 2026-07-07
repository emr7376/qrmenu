<?php if (!empty($restaurant['can_customize_theme']) && !empty($restaurant['theme_color'])): ?>
<style>body.site-public { --color-accent: <?= e($restaurant['theme_color']) ?>; }</style>
<?php endif; ?>
