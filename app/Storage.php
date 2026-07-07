<?php

// Görsel depolama soyutlaması. Öncelik sırası:
// 1) imgbb.com — kart istemez, imzalama gerektirmez, tek API key (bkz. putImgbb)
// 2) S3 uyumlu bir servis (Cloudflare R2, Backblaze B2 vb.) ayarlıysa oraya yükler
// 3) İkisi de yoksa (yerel geliştirme) eskisi gibi yerel diske yazar.
// Render gibi platformlarda disk kalıcı olmadığı için görseller her deploy'da silinir,
// bu yüzden production'da 1 veya 2'nin ayarlı olması önerilir.
// composer/vendor yok — AWS Signature V4 imzalama elle (Mailer.php'deki ham soket
// yaklaşımıyla aynı ruhta) yapılıyor.
class Storage
{
    public static function isRemoteConfigured(): bool
    {
        return OM_IMGBB_API_KEY !== ''
            || (OM_R2_ENDPOINT !== '' && OM_R2_ACCESS_KEY !== '' && OM_R2_SECRET_KEY !== '' && OM_R2_BUCKET !== '');
    }

    public static function put(string $tmpPath, string $key, string $mime): ?string
    {
        if (!is_uploaded_file($tmpPath)) {
            return null;
        }
        if (OM_IMGBB_API_KEY !== '') {
            $url = self::putImgbb($tmpPath);
            if ($url !== null) {
                return $url;
            }
            // imgbb başarısız olursa (ör. geçici kesinti) hizmeti kesmemek için diğer yollara düş.
        }
        if (OM_R2_ENDPOINT !== '' && OM_R2_ACCESS_KEY !== '' && OM_R2_SECRET_KEY !== '' && OM_R2_BUCKET !== '') {
            $url = self::putR2($tmpPath, $key, $mime);
            if ($url !== null) {
                return $url;
            }
        }
        return self::putLocal($tmpPath, $key);
    }

    private static function putImgbb(string $tmpPath): ?string
    {
        $ch = curl_init('https://api.imgbb.com/1/upload?key=' . urlencode(OM_IMGBB_API_KEY));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['image' => new CURLFile($tmpPath)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => ['Referer: https://qrmenus.onrender.com/'],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($status < 200 || $status >= 300 || $response === false) {
            error_log("imgbb yükleme hatası (HTTP $status): " . ($curlError ?: $response));
            return null;
        }

        $data = json_decode($response, true);
        return $data['data']['url'] ?? null;
    }

    private static function putLocal(string $tmpPath, string $key): string
    {
        $dest = OM_UPLOAD_DIR . '/' . $key;
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        move_uploaded_file($tmpPath, $dest);
        return OM_UPLOAD_URL . '/' . $key;
    }

    private static function putR2(string $tmpPath, string $key, string $mime): ?string
    {
        $body = file_get_contents($tmpPath);
        if ($body === false) {
            return null;
        }

        $host = OM_R2_ENDPOINT;
        $headers = self::signRequest('PUT', $key, $body, $mime, $host);

        $ch = curl_init('https://' . $host . '/' . OM_R2_BUCKET . '/' . $key);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            error_log("R2 yükleme hatası (HTTP $status): $curlError");
            return null;
        }

        return rtrim(OM_R2_PUBLIC_URL, '/') . '/' . $key;
    }

    // AWS Signature Version 4 — Cloudflare R2 ve Backblaze B2, S3 API'siyle uyumlu olduğu için aynı imzalama kullanılır.
    // R2 "auto" bölgesini kabul eder; Backblaze B2 gerçek bölge kodunu (örn. us-west-000) ister,
    // bu yüzden OM_R2_REGION ayarlanabilir (varsayılan "auto").
    private static function signRequest(string $method, string $key, string $body, string $mime, string $host): array
    {
        $region = OM_R2_REGION;
        $service = 's3';
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);

        $canonicalUri = '/' . OM_R2_BUCKET . '/' . $key;
        $canonicalHeaders = "host:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n";
        $signedHeadersList = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = "$method\n$canonicalUri\n\n$canonicalHeaders\n$signedHeadersList\n$payloadHash";

        $credentialScope = "$dateStamp/$region/$service/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        $kSecret = 'AWS4' . OM_R2_SECRET_KEY;
        $kDate = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authHeader = 'AWS4-HMAC-SHA256 Credential=' . OM_R2_ACCESS_KEY . "/$credentialScope, "
            . "SignedHeaders=$signedHeadersList, Signature=$signature";

        return [
            'Host: ' . $host,
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $amzDate,
            'Authorization: ' . $authHeader,
            'Content-Type: ' . $mime,
        ];
    }
}
