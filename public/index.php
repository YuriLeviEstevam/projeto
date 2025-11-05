<?php

declare(strict_types=1);

require _DIR_ . '/../vendor/autoload.php';

use App\Health;
use App\Db;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($method === 'GET' && $path === '/health') {
    json_out(Health::status() + ['ts' => gmdate('c')]);
}

if ($method === 'GET' && $path === '/db-check') {
    try {
        $pdo = Db::conn();
        $one = $pdo->query('SELECT 1 AS ok')->fetch();
        json_out(['db' => 'ok', 'result' => $one]);
    } catch (Throwable $e) {
        json_out(['db' => 'error', 'message' => $e->getMessage()], 500);
    }
}

if ($method === 'POST' && $path === '/patients') {
    $name = trim($_POST['name'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $birth = trim($_POST['birth_date'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $cell = trim($_POST['cellphone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $err = [];

    if (mb_strlen($name) < 3) {
        $err[] = 'Nome deve ter ao menos 3 caracteres.';
    }

    if (!preg_match('/^[A-Za-zÀ-ÿ\s]+$/u', $name)) {
        $err[] = 'Nome não deve conter números ou caracteres especiais.';
    }

    if (mb_strlen($cpf) !== 11) {
        $err[] = 'CPF deve ter 11 dígitos.';
    } elseif (!preg_match('/^\d{11}$/', $cpf)) {
        $err[] = 'CPF deve conter apenas números.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err[] = 'E-mail inválido.';
    }

    if ($birth !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) {
        $err[] = 'Data no formato YYYY-MM-DD.';
    }

    if ($birth !== '' && strtotime($birth) > time()) {
        $err[] = 'Data de nascimento não pode ser futura.';
    }

    if ($phone !== '' && !preg_match('/^[0-9]{8,15}$/', $phone)) {
        $err[] = 'Telefone (fixo) deve conter apenas números.';
    }

    if ($cell !== '' && !preg_match('/^[0-9]{8,15}$/', $cell)) {
        $err[] = 'Celular deve conter apenas números.';
    }

    if ($err) {
        $msg = '<div class="alert error"><strong>Erro:</strong><ul><li>'
            . implode('</li><li>', array_map('h', $err))
            . '</li></ul></div>';
        echo page_form($msg, compact('name', 'cpf', 'birth', 'phone', 'cell', 'email'));
        exit;
    }

    try {
        $pdo = Db::conn();
        $st = $pdo->prepare(
            'INSERT INTO patients (name, cpf, birth_date, phone, cellphone, email) VALUES (:name, :cpf, :birth, :phone, :cell, :email)'
        );
        $st->execute([
            ':name' => $name ?: null,
            ':cpf' => $cpf ?: null,
            ':birth' => $birth ?: null,
            ':phone' => $phone ?: null,
            ':cell' => $cell ?: null,
            ':email' => $email ?: null,
        ]);

        echo page_form('<div class="alert success">Paciente cadastrado com sucesso.</div>');
        exit;
    } catch (Throwable $e) {
        echo page_form(
            '<div class="alert error"><strong>Erro ao salvar:</strong> ' . h($e->getMessage()) . '</div>',
            compact('name', 'cpf', 'birth', 'phone', 'cell', 'email')
        );
        exit;
    }
}

if ($method === 'GET' && $path === '/') {
    echo page_form();
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "Not Found";

function page_form(string $flash = '', array $old = []): string
{
    $name = h($old['name'] ?? '');
    $birth = h($old['birth'] ?? '');
    $cpf = h($old['cpf'] ?? '');
    $phone = h($old['phone'] ?? '');
    $cell = h($old['cell'] ?? '');
    $email = h($old['email'] ?? '');

return <<<HTML
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastro de Pacientes</title>
  <link rel="stylesheet" href="./admin/style.css">
</head>
<body>
  <div class="container">
    <h1>Cadastro de Pacientes</h1>
    <p class="desc">Preencha seus dados para contato e agendamento.</p>{$flash}
    <form method="post" action="/patients" novalidate>
      <div><label for="name">Nome completo *</label><input type="text" id="name" name="name" value="{$name}" required></div>
      <div class="row">
        <div><label for="cpf">CPF (apenas números) *</label><input type="text" id="cpf" name="cpf" value="{$cpf}" maxlength="11" required></div>
        <div><label for="birth_date">Data de nascimento</label><input type="date" id="birth_date" name="birth_date" value="{$birth}" placeholder="YYYY-MM-DD"></div>
      </div>
      <div class="row">
        <div><label for="phone">Telefone (fixo)</label><input type="tel" id="phone" name="phone" value="{$phone}"></div>
        <div><label for="cellphone">Celular</label><input type="tel" id="cellphone" name="cellphone" value="{$cell}"></div>
      </div>
      <div><label for="email">E-mail</label><input type="email" id="email" name="email" value="{$email}"></div>
      <div><button class="primary" type="submit">Enviar cadastro</button></div>
      <p class="muted"><small class="hint">Ao enviar, você concorda com o uso dos seus dados para contato e agendamento.</small></p>
    </form>
    <p class="muted">Endpoints: <code>/health</code> • <code>/db-check</code> • <code>POST /patients</code></p>
  </div>
</body>
</html>
HTML;
}
?>
