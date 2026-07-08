<?php
// Aylık otomatik tahsilat — production'da GERÇEK bir cron ile günde bir kez çalıştırılmalı.
// Render'ın ücretsiz katmanında sunucu tarafı cron olmadığı için bunun yerine
// GET /cron/charge-subscriptions (CronController) ücretsiz bir dış cron pinger (örn. cron-job.org)
// ile günde bir kez tetiklenir. Bu script yerelde elle test/işletmek için hâlâ duruyor.

require __DIR__ . '/../config.php';
require OM_ROOT . '/app/Database.php';
require OM_ROOT . '/app/Iyzico.php';
require OM_ROOT . '/app/SubscriptionBiller.php';

echo SubscriptionBiller::run();
