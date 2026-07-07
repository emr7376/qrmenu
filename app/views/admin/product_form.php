<?php $activeNav = 'products'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2><?= $item ? 'Ürünü Düzenle' : 'Yeni Ürün' ?></h2>

<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card" style="max-width:520px;">
    <form method="post" action="<?= $item ? '/admin/products/' . (int) $item['id'] . '/edit' : '/admin/products/new' ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Ürün Adı</label>
            <input type="text" name="name" value="<?= e($item['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Açıklama</label>
            <textarea name="description"><?= e($item['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Fiyat (₺)</label>
            <input type="text" name="price" value="<?= $item ? e((string) $item['price']) : '' ?>" required>
        </div>
        <?php if ($restaurant['can_use_categories']): ?>
        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id">
                <option value="">Kategorisiz</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= isset($item['category_id']) && $item['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label>Görsel <?= $restaurant['can_upload_images'] ? '' : '(planınızda kapalı)' ?></label>
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp" <?= $restaurant['can_upload_images'] ? '' : 'disabled' ?>>
            <?php if (!empty($item['image_path'])): ?>
                <div style="margin-top:8px;"><img src="<?= e($item['image_path']) ?>" style="width:70px;height:70px;object-fit:cover;border-radius:8px;"></div>
            <?php endif; ?>
        </div>
        <?php if ($item): ?>
        <div class="form-group">
            <label><input type="checkbox" name="is_available" style="width:auto;" <?= $item['is_available'] ? 'checked' : '' ?>> Satışta / menüde görünsün</label>
        </div>
        <?php endif; ?>
        <?php if ($restaurant['can_feature_products']): ?>
        <div class="form-group">
            <label><input type="checkbox" name="is_featured" style="width:auto;" <?= !empty($item['is_featured']) ? 'checked' : '' ?>> Öne çıkan ürün olarak göster</label>
        </div>
        <?php endif; ?>
        <button type="submit" class="btn">Kaydet</button>
        <a href="/admin/products" class="btn secondary">Vazgeç</a>
    </form>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
