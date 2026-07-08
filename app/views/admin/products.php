<?php $activeNav = 'products'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<div class="toolbar">
    <h2>Ürünler</h2>
    <a href="/admin/products/new" class="btn">+ Yeni Ürün</a>
</div>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card">
    <?php if (empty($items)): ?>
        <p>Henüz ürün eklemediniz. <a href="/admin/products/new">İlk ürününüzü ekleyin</a>.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th></th><th>Ad</th><th>Kategori</th><th>Fiyat</th><th>Durum</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php if ($item['image_path']): ?><img class="thumb" src="<?= e($item['image_path']) ?>" alt=""><?php endif; ?></td>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['category_name'] ?? '-') ?></td>
                    <td><?= number_format((float) $item['price'], 2, ',', '.') ?>₺</td>
                    <td><span class="badge <?= $item['is_available'] ? 'open' : 'closed' ?>"><?= $item['is_available'] ? 'Satışta' : 'Pasif' ?></span></td>
                    <td style="white-space:nowrap;">
                        <a href="/admin/products/<?= (int) $item['id'] ?>/edit" class="btn small secondary">Düzenle</a>
                        <form method="post" action="/admin/products/<?= (int) $item['id'] ?><?= csrfField() ?>/delete" style="display:inline;" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                            <button type="submit" class="btn small danger">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
