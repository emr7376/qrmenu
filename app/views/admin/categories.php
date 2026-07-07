<?php $activeNav = 'categories'; include OM_ROOT . '/app/views/layout/admin_start.php'; ?>

<h2>Kategoriler</h2>
<p style="color:var(--color-muted)">Ürünlerinizi gruplamak için kategoriler oluşturun (ör. Başlangıçlar, Ana Yemekler, Tatlılar).</p>

<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card" style="max-width:480px;">
    <form method="post" action="/admin/categories" style="display:flex;gap:10px;align-items:flex-end;">
        <div class="form-group" style="flex:1;margin-bottom:0;">
            <label>Yeni Kategori Adı</label>
            <input type="text" name="name" required>
        </div>
        <button type="submit" class="btn">Ekle</button>
    </form>
</div>

<div class="card" style="max-width:480px;">
    <?php if (empty($categories)): ?>
        <p>Henüz kategori eklemediniz.</p>
    <?php else: ?>
        <table class="table">
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= e($cat['name']) ?></td>
                    <td style="text-align:right;">
                        <form method="post" action="/admin/categories/<?= (int) $cat['id'] ?>/delete" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? İçindeki ürünler kategorisiz kalır.');">
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
