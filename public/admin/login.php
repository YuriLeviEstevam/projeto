<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use App\Auth;

Auth::startSession();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u !== '' && $p !== '' && Auth::login($u, $p)) {
        header('Location: /admin/patients.php');
        exit;
    }
    $error = 'Usuário ou senha inválidos.';
}
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login do Estagiário</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:2rem;background:#f6f7fb}
.card{max-width:380px;margin:5vh auto;background:#fff;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
.card h1{margin:0;padding:1.2rem 1.2rem .2rem}
.form{display:grid;gap:12px;padding:1.2rem}
input{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
button{padding:12px 16px;border:0;border-radius:8px;background:#0d6efd;color:#fff;cursor:pointer}
.error{color:#b00020;background:#fde7ea;border:1px solid #f6cbd1;padding:8px 12px;border-radius:8px}
.muted{color:#666;font-size:.9rem;text-align:center;padding:8px 0 16px}
</style>
<div class="card">
  <h1>Login</h1>
  <form class="form" method="post" action="">
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>
    <div><input name="username" placeholder="Usuário" required></div>
    <div><input type="password" name="password" placeholder="Senha" required></div>
    <button>Entrar</button>
    <div class="muted">usuário: <code>estagiario</code> • senha: <code>123456</code></div>
  </form>
</div>
