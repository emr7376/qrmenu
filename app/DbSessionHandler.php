<?php

// Render'ın ücretsiz katmanında container uyuyup yeniden başladığında yerel disk
// sıfırlanıyor; dosya tabanlı PHP session'ları bu yüzden rastgele kayboluyordu
// (bkz. schema.sql'deki sessions tablosu yorumu). Session'ları DB'de tutarak
// container'ın hayat döngüsünden bağımsız hale getiriyoruz.
class DbSessionHandler implements SessionHandlerInterface
{
    public function open($savePath, $sessionName): bool
    {
        Database::get()->query(
            "CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) PRIMARY KEY,
                data MEDIUMTEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $stmt = Database::get()->prepare('SELECT data FROM sessions WHERE id = ?');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['data'] : '';
    }

    public function write($id, $data): bool
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO sessions (id, data) VALUES (?, ?) ON DUPLICATE KEY UPDATE data = ?'
        );
        $stmt->bind_param('sss', $id, $data, $data);
        return $stmt->execute();
    }

    public function destroy($id): bool
    {
        $stmt = Database::get()->prepare('DELETE FROM sessions WHERE id = ?');
        $stmt->bind_param('s', $id);
        return $stmt->execute();
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = Database::get()->prepare('DELETE FROM sessions WHERE updated_at < (NOW() - INTERVAL ? SECOND)');
        $stmt->bind_param('i', $max_lifetime);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}
