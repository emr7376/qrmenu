<?php

class Database
{
    private static ?mysqli $conn = null;

    public static function get(): mysqli
    {
        if (self::$conn === null) {
            if (OM_DB_HOST) {
                $m = mysqli_init();
                $m->ssl_set(null, null, null, null, null);
                $m->real_connect(OM_DB_HOST, OM_DB_USER, OM_DB_PASS, OM_DB_NAME, OM_DB_PORT, null, MYSQLI_CLIENT_SSL);
            } else {
                $m = new mysqli(null, OM_DB_USER, OM_DB_PASS, OM_DB_NAME, null, OM_DB_SOCKET);
            }
            if ($m->connect_error) {
                die('Veritabanı bağlantı hatası: ' . $m->connect_error);
            }
            $m->set_charset('utf8mb4');
            // Bazı yönetilen MySQL sağlayıcıları (ör. Aiven) varsayılan olarak ANSI_QUOTES modunu
            // açık getiriyor — bu, kodun her yerinde kullanılan çift tırnaklı SQL string literallerini
            // ("trial" gibi) sütun adı sanıp "Unknown column" hatası verdiriyor. Standart MySQL 8
            // varsayılanına döndürülüyor ki tüm sorgular ortam fark etmeksizin aynı şekilde çalışsın.
            $m->query("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            self::$conn = $m;
        }
        return self::$conn;
    }
}
