<?php
declare(strict_types=1);
namespace App;
use PDO; use RuntimeException;
final class Db {
  private static ?PDO $pdo = null;
  public static function conn(): PDO {
    if (self::$pdo instanceof PDO) return self::$pdo;
    $url = getenv('DATABASE_URL'); if (!$url) throw new RuntimeException('DATABASE_URL não definida.');
    $parts = parse_url($url); if (!$parts) throw new RuntimeException('DATABASE_URL inválida.');
    $user = $parts['user'] ?? ''; $pass = $parts['pass'] ?? ''; $host = $parts['host'] ?? 'localhost'; $port = $parts['port'] ?? '5432'; $db = ltrim($parts['path'] ?? '', '/');
    parse_str($parts['query'] ?? '', $q); $sslmode = $q['sslmode'] ?? 'require';
    $dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode={$sslmode}";
    self::$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    return self::$pdo;
  }
}
