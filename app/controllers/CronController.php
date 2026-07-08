<?php
// Ücretsiz dış cron pinger'lar (örn. cron-job.org) için — Render'ın ücretsiz katmanında
// gerçek bir sunucu cron'u olmadığından, günlük tahsilat bu HTTP endpoint'i üzerinden tetiklenir.
class CronController
{
    public static function chargeSubscriptions(): void
    {
        $key = $_GET['key'] ?? '';
        if (OM_CRON_SECRET === '' || !hash_equals(OM_CRON_SECRET, $key)) {
            http_response_code(403);
            echo 'forbidden';
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        echo SubscriptionBiller::run();
    }
}
