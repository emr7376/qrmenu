<?php $activeNav = 'about'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>Hakkımızda &amp; Galeri</h2>
<p style="color:var(--color-muted)">Bu bölümdeki metin ve fotoğraflar, müşterilerinizin gördüğü genel menü sayfanızda gösterilir.</p>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card" style="max-width:640px;">
    <h3>Restoran Logosu</h3>
    <div style="display:flex;gap:16px;align-items:center;">
        <?php if (!empty($restaurant['logo_path'])): ?>
            <img src="<?= e($restaurant['logo_path']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:50%;">
        <?php endif; ?>
        <form method="post" action="/admin/logo" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;"><?= csrfField() ?>
            <div class="form-group" style="flex:1;margin-bottom:0;">
                <label>Logo (jpg, png, webp)</label>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" required>
            </div>
            <button type="submit" class="btn">Yükle</button>
        </form>
    </div>
</div>

<div class="card" style="max-width:640px;">
    <form method="post" action="/admin/about"><?= csrfField() ?>
        <div class="form-group">
            <label>Hakkımızda Yazısı</label>
            <textarea name="about_text" rows="6" placeholder="Restoranınızı, mutfağınızı, hikayenizi kısaca anlatın..."><?= e($restaurant['about_text'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</div>

<div class="card" style="max-width:640px;">
    <h3>Fotoğraf Galerisi</h3>
    <p style="color:var(--color-muted)">En fazla <?= (int) $galleryLimit ?> fotoğraf ekleyebilirsiniz. Menü sayfanızda otomatik dönen bir galeri olarak gösterilir.</p>

    <form method="post" action="/admin/gallery" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:20px;"><?= csrfField() ?>
        <div class="form-group" style="flex:1;margin-bottom:0;">
            <label>Yeni Fotoğraf (jpg, png, webp)</label>
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp" required>
        </div>
        <button type="submit" class="btn">Ekle</button>
    </form>

    <?php if (empty($gallery)): ?>
        <p>Henüz fotoğraf eklemediniz.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:14px;">
            <?php foreach ($gallery as $photo): ?>
                <div style="position:relative;">
                    <img src="<?= e($photo['image_path']) ?>" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;">
                    <form method="post" action="/admin/gallery/<?= (int) $photo['id'] ?>/delete" onsubmit="return confirm('Bu fotoğrafı silmek istediğinize emin misiniz?');" style="margin-top:6px;"><?= csrfField() ?>
                        <button type="submit" class="btn small danger" style="width:100%;">Sil</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include OM_ROOT . '/app/views/layout/admin_end.php'; ?>
