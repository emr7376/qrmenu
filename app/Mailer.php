<?php

// Bağımlılıksız (composer/vendor yok) ham soket tabanlı SMTP istemcisi — app/Iyzico.php'deki
// "bilinçli bağımlılıksız istemci" tercihiyle aynı yaklaşım. Sadece giriş doğrulama kodu göndermek
// gibi tek, basit bir düz-metin e-posta ihtiyacı için yeterli; genel amaçlı bir mail kütüphanesi değil.
class Mailer
{
    public static function sendLoginCode(string $toEmail, string $toName, string $code): bool
    {
        $subject = 'QRMenü Giriş Kodunuz';
        $body = "Merhaba " . $toName . ",\r\n\r\n"
            . "Giriş yapmak için doğrulama kodunuz: " . $code . "\r\n\r\n"
            . "Bu kod 10 dakika içinde geçerliliğini yitirecektir. Bu girişi siz yapmadıysanız bu e-postayı yok sayabilirsiniz.\r\n\r\n"
            . "QRMenü";

        if (OM_SMTP_USER === '' || OM_SMTP_PASS === '') {
            error_log('[Mailer] SMTP yapılandırılmadı, kod sadece log\'a yazılıyor. ' . $toEmail . ' -> ' . $code);
            return false;
        }

        try {
            return self::sendViaSmtp($toEmail, $toName, $subject, $body);
        } catch (Exception $e) {
            error_log('[Mailer] SMTP gönderim hatası: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaSmtp(string $toEmail, string $toName, string $subject, string $body): bool
    {
        // 465 = örtük TLS (bağlantı en baştan şifreli açılır, STARTTLS adımı yok).
        // 587 = STARTTLS (düz bağlanıp sonra şifrelemeye yükseltilir). Bazı hosting
        // sağlayıcıları (Render'ın ücretsiz katmanı dahil) 587'den çıkışı engelliyor,
        // bu yüzden 465 destekleniyor.
        if ((int) OM_SMTP_PORT === 465) {
            $socket = @stream_socket_client(
                'ssl://' . OM_SMTP_HOST . ':465',
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT
            );
        } else {
            $socket = @fsockopen(OM_SMTP_HOST, OM_SMTP_PORT, $errno, $errstr, 10);
        }
        if (!$socket) {
            throw new Exception('Bağlantı kurulamadı: ' . $errstr);
        }

        self::readResponse($socket, 220);
        self::command($socket, 'EHLO qrmenu.local', 250);

        if ((int) OM_SMTP_PORT !== 465) {
            self::command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                throw new Exception('TLS başlatılamadı.');
            }
            self::command($socket, 'EHLO qrmenu.local', 250);
        }
        self::command($socket, 'AUTH LOGIN', 334);
        self::command($socket, base64_encode(OM_SMTP_USER), 334);
        self::command($socket, base64_encode(OM_SMTP_PASS), 235);

        self::command($socket, 'MAIL FROM:<' . OM_SMTP_USER . '>', 250);
        self::command($socket, 'RCPT TO:<' . $toEmail . '>', 250);
        self::command($socket, 'DATA', 354);

        $headers = [
            'From: ' . self::encodeHeader(OM_SMTP_FROM_NAME) . ' <' . OM_SMTP_USER . '>',
            'To: ' . self::encodeHeader($toName) . ' <' . $toEmail . '>',
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\r\n.\r\n", "\r\n..\r\n", $body) . "\r\n.";
        self::command($socket, $message, 250);

        self::command($socket, 'QUIT', 221);
        fclose($socket);

        return true;
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function command($socket, string $line, int $expectedCode): string
    {
        fwrite($socket, $line . "\r\n");
        return self::readResponse($socket, $expectedCode);
    }

    private static function readResponse($socket, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception('Beklenmeyen SMTP yanıtı (beklenen ' . $expectedCode . '): ' . trim($response));
        }
        return $response;
    }
}
