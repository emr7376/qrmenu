<?php

class SiteController
{
    public static function home(): void
    {
        $db = Database::get();
        $plans = $db->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);
        view('site/home', ['title' => 'QR Menü - Restoranınız için Dijital Menü', 'plans' => $plans]);
    }
}
