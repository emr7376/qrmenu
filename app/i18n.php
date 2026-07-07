<?php

$GLOBALS['PAW_TRANSLATIONS'] = [
    'tr' => [
        'nav.home' => 'Anasayfa',
        'nav.about' => 'Hakkımızda',
        'nav.menu' => 'Menü',
        'nav.contact' => 'Konum',
        'cta.view_menu' => 'Menüyü Görüntüle',
        'cta.about' => 'Hakkımızda',
        'featured' => 'Öne Çıkanlar',
        'see_full_menu' => 'Tüm menüyü gör →',
        'about_empty' => 'Bu restoran henüz hakkında bilgisi eklemedi.',
        'menu_empty' => 'Bu restoran henüz menüsüne ürün eklemedi.',
        'contact_title' => 'Konum',
        'contact_empty' => 'Bu restoran henüz konum bilgisi eklemedi.',
        'location_get_directions' => 'Yol Tarifi Al',
        'location_distance_heading' => 'Buradan Ne Kadar Uzakta?',
        'location_ask_permission' => 'Mesafeyi ve süreyi görmek için konum izni verin',
        'location_calculating' => 'Hesaplanıyor…',
        'location_denied' => 'Konum izni verilmedi.',
        'location_no_coords' => 'Bu restoran henüz haritada konumunu işaretlemedi.',
        'location_estimated' => '(tahmini, kuş uçuşu mesafeye göre)',
        'location_km_away' => 'km uzaklıkta',
        'location_minutes' => 'dk sürüyor',
        'location_find_button' => 'Mesafeyi Bul',
        'closed_title' => 'Menü Kapalı',
        'closed_body' => 'Bu menü şu anda kapalıdır. Lütfen daha sonra tekrar deneyin.',
        'not_found_title' => 'Menü bulunamadı',
        'not_found_body' => 'Bu adreste bir restoran menüsü yok.',
    ],
    'en' => [
        'nav.home' => 'Home',
        'nav.about' => 'About',
        'nav.menu' => 'Menu',
        'nav.contact' => 'Location',
        'cta.view_menu' => 'View Menu',
        'cta.about' => 'About',
        'featured' => 'Featured',
        'see_full_menu' => 'See full menu →',
        'about_empty' => "This restaurant hasn't added an about section yet.",
        'menu_empty' => "This restaurant hasn't added any menu items yet.",
        'contact_title' => 'Location',
        'contact_empty' => "This restaurant hasn't added location details yet.",
        'location_get_directions' => 'Get Directions',
        'location_distance_heading' => 'How Far Is It From Here?',
        'location_ask_permission' => 'Allow location access to see distance and travel time',
        'location_calculating' => 'Calculating…',
        'location_denied' => 'Location permission was denied.',
        'location_no_coords' => "This restaurant hasn't pinned its location yet.",
        'location_estimated' => '(estimated, based on straight-line distance)',
        'location_km_away' => 'km away',
        'location_minutes' => 'min drive',
        'location_find_button' => 'Find Distance',
        'closed_title' => 'Menu Closed',
        'closed_body' => 'This menu is currently closed. Please check back later.',
        'not_found_title' => 'Menu not found',
        'not_found_body' => 'There is no restaurant menu at this address.',
    ],
];

function menuLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en'], true)) {
        $_SESSION['menu_lang'] = $_GET['lang'];
    }
    return $_SESSION['menu_lang'] ?? 'tr';
}

function t(string $key): string
{
    $lang = menuLang();
    return $GLOBALS['PAW_TRANSLATIONS'][$lang][$key] ?? $GLOBALS['PAW_TRANSLATIONS']['tr'][$key] ?? $key;
}
