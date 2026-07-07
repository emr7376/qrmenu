<?php $bodyClass = 'site-public'; include OM_ROOT . '/app/views/layout/header.php'; ?>

<div class="landing">

<div class="hero landing-hero">
    <div class="landing-hero-glow"></div>
    <div class="container">
        <span class="eyebrow">QR Menü SaaS</span>
        <h1>Restoranınızın Menüsü<br>Artık Cebinizde</h1>
        <p>Ürün ekleyin, fiyatlandırın, QR kodunuzu masanıza koyun — menünüz her güncellemede anında yenilenir, yeniden bastırmaya gerek kalmaz.</p>
        <div class="hero-cta-row">
            <a href="/login?tab=signup" class="btn">7 Gün Ücretsiz Dene</a>
            <a href="/menu/<?= e(OM_DEMO_MENU_SLUG) ?>" class="btn secondary" target="_blank">Örnek Menüyü Gör</a>
        </div>
        <div class="hero-trust-row">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg> Kredi kartı gerekmez</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg> 5 dakikada kurulum</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg> İstediğin an iptal</span>
        </div>
    </div>
</div>

<div class="container">

    <div class="demo-preview-section">
        <span class="eyebrow center">Canlı Önizleme</span>
        <h2 class="section-title">Müşterileriniz Menünüzü Böyle Görecek</h2>
        <div class="demo-phone">
            <div class="demo-phone-screen">
                <iframe src="/menu/<?= e(OM_DEMO_MENU_SLUG) ?>" title="Örnek menü önizlemesi" loading="lazy"></iframe>
            </div>
        </div>
        <p class="demo-preview-note">Bu kurgu değil — gerçek, çalışan bir menü. <a href="/menu/<?= e(OM_DEMO_MENU_SLUG) ?>" target="_blank">Tam ekranda açın ↗</a></p>
    </div>

    <div class="features-section">
        <span class="eyebrow center">Neden QRMenü?</span>
        <h2 class="section-title">Menünüzü Yönetmenin En Kolay Yolu</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>
                </div>
                <h3>Anında Güncellenir</h3>
                <p>Fiyatı ya da ürünü değiştirin, QR kod aynı kalır — müşteri her zaman güncel menüyü görür.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg>
                </div>
                <h3>Her Ekranda Kusursuz</h3>
                <p>Telefon, tablet, masaüstü — müşterileriniz menünüzü nereden açarsa açsın şık görünür.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h7v7h-7z"/></svg>
                </div>
                <h3>Kendi QR'ınız</h3>
                <p>Markanıza uygun renkte, ister logolu QR kodunuzu saniyeler içinde indirip bastırın.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 3v18h18"/><path d="m7 14 4-4 3 3 5-6"/></svg>
                </div>
                <h3>Basit ve Şeffaf</h3>
                <p>Karmaşık ayar yok, gizli ücret yok. Kaydolun, ürünlerinizi ekleyin, menünüz yayında.</p>
            </div>
        </div>
    </div>

    <div class="steps-section">
        <span class="eyebrow center">Nasıl Çalışır</span>
        <h2 class="section-title">3 Adımda Hazır</h2>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Kaydolun</h3>
                <p>Kredi kartı gerekmeden 7 gün ücretsiz deneyin, hemen restoranınızı oluşturun.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Ürünlerinizi Ekleyin</h3>
                <p>İsim, fiyat, açıklama ve görsellerle menünüzü birkaç dakikada oluşturun.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>QR'ı Bastırın</h3>
                <p>Kendi QR kodunuzu indirin, masalarınıza koyun. Menü her değişiklikte anında güncellenir.</p>
            </div>
        </div>
    </div>

    <div class="plans-section">
        <span class="eyebrow center">Fiyatlandırma</span>
        <h2 class="section-title">Planınızı Seçin</h2>
        <div class="plans">
            <?php foreach ($plans as $i => $plan): ?>
                <div class="plan-card <?= $i === 1 ? 'featured' : '' ?>">
                    <?php if ($i === 1): ?><div class="plan-badge">En Popüler</div><?php endif; ?>
                    <h3><?= e($plan['name']) ?></h3>
                    <div class="price"><?= number_format((float) $plan['price_monthly'], 0, ',', '.') ?><span class="currency">₺</span> <span>/ ay</span></div>
                    <ul>
                        <li><?= $plan['max_products'] ? (int) $plan['max_products'] . ' ürüne kadar' : 'Sınırsız ürün' ?></li>
                        <?php if ($plan['can_upload_images']): ?><li>Ürün görseli yükleme</li><?php endif; ?>
                        <?php if ($plan['can_use_categories']): ?><li>Kategori düzenleme</li><?php endif; ?>
                        <?php if ($plan['can_feature_products']): ?><li>Öne çıkan ürün rozeti</li><?php endif; ?>
                        <?php if ($plan['can_view_analytics']): ?><li>Menü analitiği</li><?php endif; ?>
                        <?php if ($plan['can_customize_theme']): ?><li>QR rengi ve logo özelleştirme</li><?php endif; ?>
                        <li>Kendi QR kodunuz</li>
                        <li>İletişim bölümü</li>
                        <li>Menüyü aç/kapat butonu</li>
                    </ul>
                    <a href="/login?tab=signup&plan=<?= (int) $plan['id'] ?>" class="btn">Bu Planla Başla</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="cta-banner">
    <div class="container">
        <h2>Menünüzü Bugün Dijitalleştirin</h2>
        <p>Kurulum 5 dakika sürer, kredi kartı istemez. İstediğiniz zaman vazgeçebilirsiniz.</p>
        <a href="/login?tab=signup" class="btn">7 Gün Ücretsiz Dene</a>
    </div>
</div>

</div>

<?php include OM_ROOT . '/app/views/layout/footer.php'; ?>
