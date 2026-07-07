<?php

class Database
{
    private static ?mysqli $conn = null;

    public static function get(): mysqli
    {
        if (self::$conn === null) {
            if (OM_DB_HOST) {
                $m = new mysqli(OM_DB_HOST, OM_DB_USER, OM_DB_PASS, OM_DB_NAME, OM_DB_PORT);
            } else {
                $m = new mysqli(null, OM_DB_USER, OM_DB_PASS, OM_DB_NAME, null, OM_DB_SOCKET);
            }
            if ($m->connect_error) {
                die('Veritabanı bağlantı hatası: ' . $m->connect_error);
            }
            $m->set_charset('utf8mb4');
            self::$conn = $m;
        }
        return self::$conn;
    }
}
