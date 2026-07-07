<?php
// Aylık otomatik tahsilat — production'da GERÇEK bir cron ile günde bir kez çalıştırılmalı, örn:
//   0 3 * * * /path/to/php /path/to/onlinemenu/bin/charge-subscriptions.php >> /path/to/onlinemenu/storage/charge.log 2>&1
// Bu Mac'teki yerel `php -S` sunucusu sürekli açık kalmadığı ve burada cron kurulu olmadığı için
// yerelde elle çalıştırılıp test edilir; canlıya alınırken hosting'de gerçek bir cron şart.

require __DIR__ . '/../config.php';
require OM_ROOT . '/app/Database.php';
require OM_ROOT . '/app/Iyzico.php';

const MAX_RETRY_BEFORE_EXPIRE = 3;

$db = Database::get();

$due = $db->query(
    "SELECT r.*, p.price_monthly, p.name AS plan_name
     FROM restaurants r JOIN plans p ON p.id = r.plan_id
     WHERE r.iyzico_card_token IS NOT NULL
       AND r.subscription_status IN ('active', 'trial')
       AND (
            (r.subscription_status = 'active' AND r.next_billing_at IS NOT NULL AND r.next_billing_at <= NOW())
         OR (r.subscription_status = 'trial' AND r.trial_ends_at <= NOW())
       )"
)->fetch_all(MYSQLI_ASSOC);

echo count($due) . " restoran için tahsilat denenecek.\n";

foreach ($due as $restaurant) {
    $amount = (float) $restaurant['price_monthly'];
    $result = Iyzico::chargeStoredCard($restaurant, $amount);
    $success = ($result['status'] ?? '') === 'success';

    $log = $db->prepare('INSERT INTO payment_transactions (restaurant_id, amount, status, iyzico_payment_id, error_message) VALUES (?, ?, ?, ?, ?)');
    $status = $success ? 'success' : 'failure';
    $paymentId = $result['paymentId'] ?? null;
    $errorMessage = $success ? null : ($result['errorMessage'] ?? 'Bilinmeyen hata');
    $log->bind_param('idsss', $restaurant['id'], $amount, $status, $paymentId, $errorMessage);
    $log->execute();

    if ($success) {
        $nextBillingAt = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');
        $update = $db->prepare("UPDATE restaurants SET subscription_status = 'active', next_billing_at = ?, payment_retry_count = 0 WHERE id = ?");
        $update->bind_param('si', $nextBillingAt, $restaurant['id']);
        $update->execute();
        echo "OK  #{$restaurant['id']} {$restaurant['name']} - {$amount}₺ tahsil edildi, sıradaki: {$nextBillingAt}\n";
        continue;
    }

    $retryCount = (int) $restaurant['payment_retry_count'] + 1;
    if ($retryCount >= MAX_RETRY_BEFORE_EXPIRE) {
        $update = $db->prepare("UPDATE restaurants SET subscription_status = 'expired', payment_retry_count = ? WHERE id = ?");
        $update->bind_param('ii', $retryCount, $restaurant['id']);
        $update->execute();
        echo "FAIL #{$restaurant['id']} {$restaurant['name']} - {$retryCount}. denemede de başarısız, üyelik 'expired' yapıldı.\n";
    } else {
        $update = $db->prepare('UPDATE restaurants SET payment_retry_count = ? WHERE id = ?');
        $update->bind_param('ii', $retryCount, $restaurant['id']);
        $update->execute();
        echo "FAIL #{$restaurant['id']} {$restaurant['name']} - deneme {$retryCount}/" . MAX_RETRY_BEFORE_EXPIRE . ", yarın tekrar denenecek.\n";
    }
}

echo "Bitti.\n";
