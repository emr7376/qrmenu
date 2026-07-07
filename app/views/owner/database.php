<?php
$tableLabels = [
    'restaurants' => 'Restoranlar', 'plans' => 'Planlar', 'menu_categories' => 'Menü Kategorileri',
    'menu_items' => 'Ürünler', 'menu_visits' => 'Menü Ziyaretleri', 'restaurant_gallery' => 'Galeri Fotoğrafları',
    'admins' => 'Yöneticiler',
];
$numericColumns = ['id', 'sort_order', 'max_products'];
$tableDescriptions = [
    'restaurants' => 'Platforma kayıtlı her restoran ve plan/abonelik bilgisi.',
    'plans' => 'Restoran planları ve her planın hangi özelliklere sahip olduğu.',
    'menu_categories' => 'Restoranların menülerindeki kategoriler (Başlangıçlar, Tatlılar vb.).',
    'menu_items' => 'Restoranların menülerine ekledikleri ürünler.',
    'menu_visits' => 'Bir public menünün her görüntülenmesinde düşen kayıt (analitik için).',
    'restaurant_gallery' => 'Restoranların "Hakkımızda" sayfasındaki fotoğraf galerisi.',
    'admins' => 'Süper admin (platform sahibi) giriş hesapları.',
];

$columnLabels = [
    'id' => 'ID', 'name' => 'Ad', 'slug' => 'Slug', 'email' => 'E-posta',
    'password_hash' => 'Şifre', 'plan_id' => 'Plan', 'restaurant_id' => 'Restoran',
    'category_id' => 'Kategori', 'menu_item_id' => 'Ürün',
    'subscription_status' => 'Abonelik Durumu', 'trial_ends_at' => 'Deneme Bitiş',
    'is_open' => 'Menü Açık', 'qr_color' => 'QR Rengi', 'qr_logo_path' => 'QR Logo',
    'logo_path' => 'Logo', 'contact_phone' => 'Telefon', 'contact_address' => 'Adres',
    'contact_instagram' => 'Instagram', 'contact_whatsapp' => 'WhatsApp',
    'created_at' => 'Oluşturulma', 'updated_at' => 'Güncellenme', 'price_monthly' => 'Aylık Fiyat',
    'monthly_price' => 'Aylık Fiyat', 'max_products' => 'Ürün Limiti', 'can_upload_images' => 'Görsel Yükleme',
    'can_use_categories' => 'Kategoriler', 'can_customize_theme' => 'Tema/QR Özelleştirme',
    'can_feature_products' => 'Öne Çıkan Ürün', 'can_view_analytics' => 'Analitik',
    'sort_order' => 'Sıra', 'is_active' => 'Aktif',
    'description' => 'Açıklama', 'price' => 'Fiyat', 'image_path' => 'Görsel', 'is_available' => 'Satışta',
    'is_featured' => 'Öne Çıkan', 'visited_at' => 'Ziyaret Zamanı',
    'phone' => 'Telefon',
];

$booleanColumns = ['is_open', 'can_upload_images', 'can_use_categories', 'can_customize_theme', 'can_feature_products',
    'can_view_analytics', 'is_active', 'is_available', 'is_featured'];
$priceColumns = ['price_monthly', 'monthly_price', 'price'];
$dateColumns = ['created_at', 'updated_at', 'trial_ends_at', 'visited_at'];
$lookupColumns = array_keys($lookups);

function paw_owner_db_format($col, $value, $ctx)
{
    if ($value === null || $value === '') {
        return '<span style="color:var(--color-muted);">—</span>';
    }
    if (in_array($col, $ctx['lookupColumns'], true)) {
        $label = $ctx['lookups'][$col][$value] ?? null;
        return $label ? e($label) . ' <span style="color:var(--color-muted);">(#' . e((string) $value) . ')</span>' : '#' . e((string) $value);
    }
    if (in_array($col, $ctx['booleanColumns'], true)) {
        return $value ? '<span class="badge open">Evet</span>' : '<span class="badge closed">Hayır</span>';
    }
    if (in_array($col, $ctx['priceColumns'], true)) {
        return number_format((float) $value, 2, ',', '.') . '₺';
    }
    if (in_array($col, $ctx['dateColumns'], true)) {
        try {
            return (new DateTime($value))->format('d.m.Y H:i');
        } catch (Exception $e) {
            return e((string) $value);
        }
    }
    return e((string) $value);
}

$formatCtx = compact('lookupColumns', 'lookups', 'booleanColumns', 'priceColumns', 'dateColumns');
?>
<?php $activeNav = 'database'; include OM_ROOT . '/app/views/owner/_start.php'; ?>

    <div class="owner-section-title">
        <h2>Veritabanı</h2>
        <span>Salt okunur görünüm, en fazla son 200 kayıt gösterilir. Şifreler gizlenir.</span>
    </div>

    <div class="db-tabs">
        <?php foreach ($tables as $t): ?>
            <a href="/superadmin/database?table=<?= e($t) ?>" class="btn small <?= $t === $currentTable ? '' : 'secondary' ?>"><?= e($tableLabels[$t] ?? $t) ?></a>
        <?php endforeach; ?>
    </div>

    <p style="color:var(--color-muted);"><?= e($tableDescriptions[$currentTable] ?? '') ?></p>
    <p style="color:var(--color-muted);">Toplam <?= (int) $totalRows ?> kayıt<?= $totalRows > 200 ? ' (ilk 200 gösteriliyor)' : '' ?></p>

    <?php if (empty($rows)): ?>
        <div class="card"><p>Bu tabloda kayıt yok.</p></div>
    <?php elseif ($groupedByRestaurant !== null): ?>
        <?php $nestedColumns = array_values(array_diff($columns, ['restaurant_id'])); ?>
        <?php foreach ($groupedByRestaurant as $restaurantId => $restRows): ?>
            <?php $restaurantName = $lookups['restaurant_id'][$restaurantId] ?? ('Restoran #' . $restaurantId); ?>
            <div class="card" style="padding:0;overflow:hidden;">
                <div class="db-group-head" onclick="pawToggleRest(<?= (int) $restaurantId ?>)">
                    <strong><?= e($restaurantName) ?></strong>
                    <span style="color:var(--color-muted);"><?= count($restRows) ?> kayıt ▾</span>
                </div>
                <div id="db-rest-<?= (int) $restaurantId ?>" style="display:none;border-top:1px solid var(--color-border);overflow-x:auto;">
                    <table class="table table-zebra">
                        <thead>
                            <tr><?php foreach ($nestedColumns as $col): ?><th style="white-space:nowrap;<?= in_array($col, $numericColumns, true) ? 'text-align:right;' : '' ?>"><?= e($columnLabels[$col] ?? $col) ?></th><?php endforeach; ?></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($restRows as $row): ?>
                            <?php $isCategoryRow = $currentTable === 'menu_categories'; ?>
                            <tr <?= $isCategoryRow ? 'style="cursor:pointer;" onclick="pawToggleCat(' . (int) $row['id'] . ')"' : '' ?>>
                                <?php foreach ($nestedColumns as $col): ?>
                                    <td class="<?= in_array($col, $numericColumns, true) ? 'num' : '' ?>" style="white-space:nowrap;max-width:220px;overflow:hidden;text-overflow:ellipsis;">
                                        <?= paw_owner_db_format($col, $row[$col], $formatCtx) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php if ($isCategoryRow): ?>
                                <tr id="db-cat-items-<?= (int) $row['id'] ?>" style="display:none;">
                                    <td colspan="<?= count($nestedColumns) ?>" style="background:var(--color-bg);padding:14px 20px;">
                                        <?php $catItems = $itemsByCategory[$row['id']] ?? []; ?>
                                        <?php if (empty($catItems)): ?>
                                            <span style="color:var(--color-muted);">Bu kategoride henüz ürün yok.</span>
                                        <?php else: ?>
                                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                                <?php foreach ($catItems as $item): ?>
                                                    <span class="badge <?= $item['is_available'] ? 'open' : 'closed' ?>">
                                                        <?= e($item['name']) ?> — <?= number_format((float) $item['price'], 0, ',', '.') ?>₺
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
        <script>
            function pawToggleRest(id) {
                const el = document.getElementById('db-rest-' + id);
                if (!el) return;
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }
            function pawToggleCat(id) {
                const row = document.getElementById('db-cat-items-' + id);
                if (!row) return;
                row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            }
        </script>
    <?php else: ?>
        <div class="card" style="overflow-x:auto;">
            <table class="table table-zebra">
                <thead>
                    <tr><?php foreach ($columns as $col): ?><th style="white-space:nowrap;<?= in_array($col, $numericColumns, true) ? 'text-align:right;' : '' ?>"><?= e($columnLabels[$col] ?? $col) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td class="<?= in_array($col, $numericColumns, true) ? 'num' : '' ?>" style="white-space:nowrap;max-width:220px;overflow:hidden;text-overflow:ellipsis;">
                                <?= paw_owner_db_format($col, $row[$col], $formatCtx) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php include OM_ROOT . '/app/views/owner/_end.php'; ?>
