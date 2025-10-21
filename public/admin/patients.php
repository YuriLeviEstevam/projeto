<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use App\Auth;
use App\Db;

Auth::requireLogin();

$pdo = Db::conn();
$st  = $pdo->query('SELECT id, name, birth_date, phone, cellphone, email FROM patients ORDER BY id DESC LIMIT 100');
$rows = $st->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pacientes</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:2rem}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #eee;padding:10px;text-align:left}
.badge{background:#eef;padding:2px 8px;border-radius:6px}
a.btn{background:#0d6efd;color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px}
</style>
<div class="header">
  <h1>Pacientes <span class="badge"><?= count($rows) ?></span></h1>
  <div><a class="btn" href="/admin/logout.php">Sair</a></div>
</div>

<table class="table">
  <thead>
    <tr><th>ID</th><th>Nome</th><th>Nascimento</th><th>Telefone</th><th>Celular</th><th>E-mail</th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= htmlspecialchars($r['name'] ?? '', ENT_QUOTES) ?></td>
      <td><?= htmlspecialchars($r['birth_date'] ?? '', ENT_QUOTES) ?></td>
      <td><?= htmlspecialchars($r['phone'] ?? '', ENT_QUOTES) ?></td>
      <td><?= htmlspecialchars($r['cellphone'] ?? '', ENT_QUOTES) ?></td>
      <td><?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
