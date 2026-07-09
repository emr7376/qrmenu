<?php

// Render'ın ücretsiz katmanında container uyuyup yeniden başladığında yerel disk
// sıfırlanıyor; dosya tabanlı PHP session'ları bu yüzden rastgele kayboluyordu
// (bkz. schema.sql'deki sessions tablosu yorumu). Session'ları DB'de tutarak
// container'ın hayat döngüsünden bağımsız hale getiriyoruz.
class DbSessionHandler implements SessionHandlerInterface
{
    // read()'de görülen son değer — write() bununla aynıysa DB'ye dokunmuyoruz.
    // Oturum verisi değişmeyen (çoğu) isteklerde bir round-trip'i tamamen elimine ediyor.
    private ?string $lastRead = null;

    public function open($savePath, $sessionName): bool
    {
        // Tablo schema.sql'de tanımlı ve ilk deploy'da zaten oluşturuldu — burada her
        // istekte CREATE TABLE çalıştırmak (eskiden yapılıyordu) gereksiz bir DDL
        // sorgusuyla her sayfa yüklemesine gecikme ekliyordu.
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
        $this->lastRead = $row ? $row['data'] : '';
        return $this->lastRead;
    }

    public function write($id, $data): bool
    {
        if ($data === $this->lastRead) {
            return true;
        }
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
