<?php $activeNav = 'restaurants'; include OM_ROOT . '/app/views/owner/_start.php'; ?>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

<div class="stat-grid">
    <div class="stat-tile">
        <div class="stat-tile-label">Toplam Üyelik</div>
        <div class="stat-tile-value"><?= (int) $counts['total'] ?></div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">Deneme</div>
        <div class="stat-tile-value trial"><?= (int) $counts['trial'] ?></div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">Aktif</div>
        <div class="stat-tile-value active"><?= (int) $counts['active'] ?></div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">Süresi Doldu</div>
        <div class="stat-tile-value expired"><?= (int) $counts['expired'] ?></div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-label">İptal</div>
        <div class="stat-tile-value canceled"><?= (int) $counts['canceled'] ?></div>
    </div>
</div>

<div class="owner-section-title">
    <h2>Gelir</h2>
    <span>Sadece "Aktif" durumundaki restoranlar sayılır</span>
</div>
<div class="stat-grid">
    <div class="stat-tile">
        <div class="stat-tile-label">Toplam Aylık Gelir</div>
        <div class="stat-tile-value" style="color:var(--color-primary);"><?= number_format($totalRevenue, 0, ',', '.') ?>₺</div>
    </div>
    <?php foreach ($revenueByPlan as $planName => $data): ?>
        <div class="stat-tile">
            <div class="stat-tile-label"><?= e($planName) ?> (<?= (int) $data['count'] ?> restoran)</div>
            <div class="stat-tile-value"><?= number_format($data['revenue'], 0, ',', '.') ?>₺</div>
        </div>
    <?php endforeach; ?>
</div>

<div class="toolbar" style="margin-top:40px;">
    <h2 style="margin:0;">Restoranlar (<?= count($restaurants) ?>)</h2>
    <a href="/superadmin/restaurants/new" class="btn">+ Yeni Restoran Ekle</a>
</div>
<div class="card" style="overflow-x:auto;">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>Restoran</th><th>E-posta</th><th>Plan</th><th>Durum</th><th>Ürün</th><th>Deneme Bitiş</th><th>Yönetim</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($restaurants as $r): ?>
            <?php $statusLabels = ['trial' => 'Deneme', 'active' => 'Aktif', 'expired' => 'Süresi Doldu', 'canceled' => 'İptal'];
                  $statusBadgeClass = ['trial' => 'trial', 'active' => 'active', 'expired' => 'expired', 'canceled' => 'closed']; ?>
            <tr>
                <td><?= e($r['name']) ?><br><small style="color:var(--color-muted)"><a href="/menu/<?= e($r['slug']) ?>" target="_blank"><?= e($r['slug']) ?></a></small></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e($r['plan_name']) ?></td>
                <td><span class="badge <?= $statusBadgeClass[$r['subscription_status']] ?>"><?= $statusLabels[$r['subscription_status']] ?></span></td>
                <td><?= (int) $r['product_count'] ?></td>
                <td><?= (new DateTime($r['trial_ends_at']))->format('d.m.Y') ?></td>
                <td>
                    <form method="post" action="/superadmin/restaurants/<?= (int) $r['id'] ?>" style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                        <select name="plan_id">
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= (int) $p['id'] ?>" <?= $p['id'] == $r['plan_id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="subscription_status">
                            <?php foreach (['trial' => 'Deneme', 'active' => 'Aktif', 'expired' => 'Süresi Doldu', 'canceled' => 'İptal'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $r['subscription_status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label style="font-weight:400;font-size:0.85rem;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="is_open" style="width:auto;" <?= $r['is_open'] ? 'checked' : '' ?>> Menü açık</label>
                        <button type="submit" class="btn small">Kaydet</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include OM_ROOT . '/app/views/owner/_end.php'; ?>
