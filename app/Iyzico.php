<?php

// iyzico REST API istemcisi (bağımlılıksız, düz cURL — projenin "framework yok" ilkesine uygun).
// Auth şeması: IYZWSv2 (HMACSHA256). Detay: https://docs.iyzico.com/en/getting-started/preliminaries/authentication/hmacsha256-auth
class Iyzico
{
    private static function randomKey(): string
    {
        return (string) (int) (microtime(true) * 1000) . random_int(100000000, 999999999);
    }

    private static function authHeader(string $uriPath, string $body, string $randomKey): string
    {
        $payload = $randomKey . $uriPath . $body;
        $signature = hash_hmac('sha256', $payload, OM_IYZICO_SECRET_KEY);
        $authorizationString = 'apiKey:' . OM_IYZICO_API_KEY . '&randomKey:' . $randomKey . '&signature:' . $signature;
        return 'IYZWSv2 ' . base64_encode($authorizationString);
    }

    private static function request(string $uriPath, array $body): array
    {
        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $randomKey = self::randomKey();

        $ch = curl_init(OM_IYZICO_BASE_URL . $uriPath);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: ' . self::authHeader($uriPath, $json, $randomKey),
                'x-iyzi-rnd: ' . $randomKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['status' => 'failure', 'errorMessage' => 'Bağlantı hatası: ' . $error];
        }
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => 'failure', 'errorMessage' => 'Geçersiz yanıt'];
    }

    private static function buyerFor(array $restaurant): array
    {
        $nameParts = explode(' ', trim($restaurant['name']), 2);
        return [
            'id' => 'restaurant-' . $restaurant['id'],
            'name' => $nameParts[0] !== '' ? $nameParts[0] : 'Restoran',
            'surname' => $nameParts[1] ?? 'Sahibi',
            'identityNumber' => $restaurant['billing_identity_number'],
            'email' => $restaurant['email'],
            'gsmNumber' => $restaurant['contact_phone'] ?: '5000000000',
            'registrationAddress' => $restaurant['contact_address'] ?: $restaurant['billing_city'],
            'city' => $restaurant['billing_city'],
            'country' => 'Turkey',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ];
    }

    private static function addressFor(array $restaurant): array
    {
        return [
            'contactName' => $restaurant['name'],
            'city' => $restaurant['billing_city'],
            'country' => 'Turkey',
            'address' => $restaurant['contact_address'] ?: $restaurant['billing_city'],
        ];
    }

    private static function basketItemFor(array $restaurant, float $amount): array
    {
        return [[
            'id' => 'plan-' . $restaurant['plan_id'],
            'name' => 'QR Menü SaaS - ' . $restaurant['plan_name'] . ' Aylık Abonelik',
            'category1' => 'Abonelik',
            'itemType' => 'VIRTUAL',
            'price' => number_format($amount, 2, '.', ''),
        ]];
    }

    // İlk ödeme + kart kaydı için iyzico'nun barındırdığı ödeme sayfasını başlatır.
    public static function initCheckoutForm(array $restaurant, float $amount, string $callbackUrl): array
    {
        $price = number_format($amount, 2, '.', '');
        return self::request('/payment/iyzipos/checkoutform/initialize/auth/ecom', [
            'locale' => 'tr',
            'conversationId' => 'restaurant-' . $restaurant['id'] . '-' . time(),
            'price' => $price,
            'paidPrice' => $price,
            'currency' => 'TRY',
            'basketId' => 'sub-' . $restaurant['id'],
            'paymentGroup' => 'SUBSCRIPTION',
            'callbackUrl' => $callbackUrl,
            'buyer' => self::buyerFor($restaurant),
            'billingAddress' => self::addressFor($restaurant),
            'basketItems' => self::basketItemFor($restaurant, $amount),
        ]);
    }

    // Callback sonrası ödeme sonucunu ve (kart kaydı yapıldıysa) cardUserKey/cardToken'ı getirir.
    public static function retrieveCheckoutForm(string $token): array
    {
        return self::request('/payment/iyzipos/checkoutform/auth/ecom/detail', [
            'locale' => 'tr',
            'conversationId' => 'retrieve-' . $token,
            'token' => $token,
        ]);
    }

    // Kayıtlı kartla (kullanıcı ekranda değilken) otomatik/tekrarlayan tahsilat.
    public static function chargeStoredCard(array $restaurant, float $amount): array
    {
        $price = number_format($amount, 2, '.', '');
        return self::request('/payment/auth', [
            'locale' => 'tr',
            'conversationId' => 'charge-' . $restaurant['id'] . '-' . time(),
            'price' => $price,
            'paidPrice' => $price,
            'currency' => 'TRY',
            'basketId' => 'sub-' . $restaurant['id'],
            'paymentGroup' => 'SUBSCRIPTION',
            'paymentCard' => [
                'cardUserKey' => $restaurant['iyzico_card_user_key'],
                'cardToken' => $restaurant['iyzico_card_token'],
            ],
            'buyer' => self::buyerFor($restaurant),
            'billingAddress' => self::addressFor($restaurant),
            'basketItems' => self::basketItemFor($restaurant, $amount),
        ]);
    }
}
