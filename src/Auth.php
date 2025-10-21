<?php
declare(strict_types=1);
namespace App;

use PDO;

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure'   => true,   // em produção é https (Render)
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function login(string $username, string $password): bool
    {
        self::startSession();
        $pdo = Db::conn();
        $st = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :u');
        $st->execute([':u' => $username]);
        $row = $st->fetch();
        if (!$row) return false;

        $hash = (string)($row['password_hash'] ?? '');
        $ok = false;

        // Se o hash for gerado no PHP (password_hash)
        if (str_starts_with($hash, '$2y$')) {
            $ok = password_verify($password, $hash);
        } else {
            // Fallback: valida com crypt() do Postgres (pgcrypto)
            $chk = $pdo->prepare('SELECT crypt(:p, :h) = :h AS ok');
            $chk->execute([':p' => $password, ':h' => $hash]);
            $ok = (bool)($chk->fetch()['ok'] ?? false);
        }

        if ($ok) {
            $_SESSION['uid'] = (int)$row['id'];
            $_SESSION['uname'] = (string)$row['username'];
        }
        return $ok;
    }

    public static function requireLogin(): void
    {
        self::startSession();
        if (!isset($_SESSION['uid'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
