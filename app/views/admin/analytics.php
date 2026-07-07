<?php $activeNav = 'analytics'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>Analitik</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:20px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--color-primary);"><?= (int) $today ?></div>
        <div style="color:var(--color-muted);">Bugün</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--color-primary);"><?= (int) $week ?></div>
        <div style="color:var(--color-muted);">Son 7 Gün</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--color-primary);"><?= (int) $total ?></div>
        <div style="color:var(--color-muted);">Toplam Görüntülenme</div>
    </div>
</div>

<div class="card">
    <h3>Son 14 Gün</h3>
    <?php if (empty($daily)): ?>
        <p style="color:var(--color-muted);">Henüz yeterli veri yok.</p>
    <?php else: ?>
        <?php $max = max(array_column($daily, 'c')) ?: 1; ?>
        <div style="display:flex;align-items:flex-end;gap:6px;height:140px;">
            <?php foreach ($daily as $d): ?>
                <div style="flex:1;text-align:center;">
                    <div style="background:var(--color-primary);border-radius:4px 4px 0 0;height:<?= max(4, (int) ($d['c'] / $max * 110)) ?>px;" title="<?= (int) $d['c'] ?> görüntülenme"></div>
                    <div style="font-size:0.7rem;color:var(--color-muted);margin-top:4px;"><?= date('d.m', strtotime($d['day'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
