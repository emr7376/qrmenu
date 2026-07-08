<?php

class SiteController
{
    public static function home(): void
    {
        $db = Database::get();
        $plans = $db->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'QRMenü',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => 'Restoranlar için QR kodlu dijital menü yönetim sistemi.',
            'url' => canonicalUrl('/'),
        ];
        if (isset($plans[0]['price_monthly'])) {
            $structuredData['offers'] = [
                '@type' => 'Offer',
                'price' => (string) $plans[0]['price_monthly'],
                'priceCurrency' => 'TRY',
            ];
        }

        view('site/home', [
            'title' => 'QR Menü - Restoranınız için Dijital Menü | QRMenü',
            'metaDescription' => 'Restoranınız için QR kodlu dijital menü oluşturun. Ürün ekleyin, fiyatlandırın, QR kodunuzu masalarınıza koyun - menünüz her güncellemede anında yenilenir, yeniden bastırmaya gerek kalmaz. 7 gün ücretsiz deneyin, kredi kartı gerekmez.',
            'plans' => $plans,
            'structuredData' => $structuredData,
        ]);
    }
}
