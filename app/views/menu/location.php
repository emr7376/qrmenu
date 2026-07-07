<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" href="<?= !empty($restaurant['logo_path']) ? e($restaurant['logo_path']) : '/assets/favicon.svg' ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php include OM_ROOT . '/app/views/menu/_theme_style.php'; ?>
</head>
<body class="site-public">
<div class="page-header">
    <?php include OM_ROOT . '/app/views/menu/_nav.php'; ?>
    <div class="page-header-content">
        <h1><?= e(t('contact_title')) ?></h1>
    </div>
</div>

<div class="menu-public">
    <?php $hasCoords = $restaurant['latitude'] !== null && $restaurant['longitude'] !== null; ?>
    <?php
        if ($hasCoords) {
            $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($restaurant['latitude'] . ',' . $restaurant['longitude']);
        } elseif (!empty($restaurant['contact_address'])) {
            $directionsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($restaurant['contact_address']);
        } else {
            $directionsUrl = null;
        }
    ?>

    <?php if ($hasCoords): ?>
        <div class="map-embed">
            <iframe
                src="https://www.google.com/maps?q=<?= urlencode($restaurant['latitude'] . ',' . $restaurant['longitude']) ?>&z=15&output=embed"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    <?php elseif (!empty($restaurant['contact_address'])): ?>
        <div class="map-embed">
            <iframe
                src="https://www.google.com/maps?q=<?= urlencode($restaurant['contact_address']) ?>&z=15&output=embed"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    <?php endif; ?>

    <?php if ($restaurant['contact_phone'] || $restaurant['contact_address'] || $restaurant['contact_instagram'] || $restaurant['contact_whatsapp'] || $directionsUrl): ?>
        <div class="contact-box">
            <h3><?= e(t('contact_title')) ?></h3>
            <?php if ($restaurant['contact_address']): ?><p><?= menuIcon('pin') ?> <?= e($restaurant['contact_address']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_phone']): ?><p><?= menuIcon('phone') ?> <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $restaurant['contact_phone'])) ?>"><?= e($restaurant['contact_phone']) ?></a></p><?php endif; ?>
            <?php if ($restaurant['contact_whatsapp']): ?><p><?= menuIcon('chat') ?> WhatsApp: <?= e($restaurant['contact_whatsapp']) ?></p><?php endif; ?>
            <?php if ($restaurant['contact_instagram']): ?><p><?= menuIcon('camera') ?> <?= e($restaurant['contact_instagram']) ?></p><?php endif; ?>
            <?php if ($directionsUrl): ?>
                <p style="margin-top:16px;"><a href="<?= e($directionsUrl) ?>" target="_blank" class="btn"><?= menuIcon('compass') ?> <?= e(t('location_get_directions')) ?></a></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--color-muted);"><?= e(t('contact_empty')) ?></p>
    <?php endif; ?>

    <?php if ($hasCoords): ?>
        <div class="contact-box" id="distance-box" style="margin-top:24px;">
            <h3><?= e(t('location_distance_heading')) ?></h3>
            <p id="distance-status" style="color:var(--color-muted);"><?= e(t('location_ask_permission')) ?></p>
            <div id="distance-result" style="display:none;">
                <div class="distance-value"><span id="distance-km"></span> <?= e(t('location_km_away')) ?></div>
                <div class="distance-time"><span id="distance-min"></span> <?= e(t('location_minutes')) ?></div>
                <p id="distance-note" style="color:var(--color-muted);font-size:0.8rem;margin-top:6px;"></p>
            </div>
            <button type="button" class="btn secondary" id="distance-btn" onclick="pawFindDistance()"><?= menuIcon('pin') ?> <?= e(t('location_find_button')) ?></button>
        </div>
    <?php else: ?>
        <p style="color:var(--color-muted);margin-top:20px;"><?= e(t('location_no_coords')) ?></p>
    <?php endif; ?>
</div>

<?php if ($hasCoords): ?>
<script>
    var PAW_REST_LAT = <?= json_encode((float) $restaurant['latitude']) ?>;
    var PAW_REST_LNG = <?= json_encode((float) $restaurant['longitude']) ?>;
    var PAW_GOOGLE_KEY = <?= json_encode(OM_GOOGLE_MAPS_API_KEY) ?>;
    var PAW_T_DENIED = <?= json_encode(t('location_denied')) ?>;
    var PAW_T_CALC = <?= json_encode(t('location_calculating')) ?>;
    var PAW_T_ESTIMATED = <?= json_encode(t('location_estimated')) ?>;

    function pawHaversineKm(lat1, lng1, lat2, lng2) {
        var R = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    function pawShowResult(km, minutes, estimated) {
        document.getElementById('distance-status').style.display = 'none';
        document.getElementById('distance-btn').style.display = 'none';
        document.getElementById('distance-result').style.display = 'block';
        document.getElementById('distance-km').textContent = km.toFixed(1).replace('.', ',');
        document.getElementById('distance-min').textContent = Math.round(minutes);
        document.getElementById('distance-note').textContent = estimated ? PAW_T_ESTIMATED : '';
    }

    function pawFetchGoogleDistance(userLat, userLng) {
        var url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' + userLat + ',' + userLng +
            '&destinations=' + PAW_REST_LAT + ',' + PAW_REST_LNG + '&mode=driving&key=' + PAW_GOOGLE_KEY;
        fetch(url).then(function (r) { return r.json(); }).then(function (data) {
            var el = data.rows && data.rows[0] && data.rows[0].elements && data.rows[0].elements[0];
            if (el && el.status === 'OK') {
                pawShowResult(el.distance.value / 1000, el.duration.value / 60, false);
            } else {
                var km = pawHaversineKm(userLat, userLng, PAW_REST_LAT, PAW_REST_LNG);
                pawShowResult(km, km / 35 * 60, true);
            }
        }).catch(function () {
            var km = pawHaversineKm(userLat, userLng, PAW_REST_LAT, PAW_REST_LNG);
            pawShowResult(km, km / 35 * 60, true);
        });
    }

    function pawFindDistance() {
        document.getElementById('distance-status').textContent = PAW_T_CALC;
        if (!navigator.geolocation) {
            document.getElementById('distance-status').textContent = PAW_T_DENIED;
            return;
        }
        navigator.geolocation.getCurrentPosition(function (pos) {
            var userLat = pos.coords.latitude;
            var userLng = pos.coords.longitude;
            if (PAW_GOOGLE_KEY) {
                pawFetchGoogleDistance(userLat, userLng);
            } else {
                var km = pawHaversineKm(userLat, userLng, PAW_REST_LAT, PAW_REST_LNG);
                pawShowResult(km, km / 35 * 60, true);
            }
        }, function () {
            document.getElementById('distance-status').textContent = PAW_T_DENIED;
        });
    }
</script>
<?php endif; ?>

<?php include OM_ROOT . '/app/views/menu/_footer.php'; ?>
</body>
</html>
