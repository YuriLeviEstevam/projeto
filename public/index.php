<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
use App\Health; use App\Db;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function json_out($data, int $code = 200): void { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
if ($method==='GET' && $path==='/health') { json_out(Health::status()+['ts'=>gmdate('c')]); }
if ($method==='GET' && $path==='/db-check') { try { $pdo=Db::conn(); $one=$pdo->query('SELECT 1 AS ok')->fetch(); json_out(['db'=>'ok','result'=>$one]); } catch (Throwable $e) { json_out(['db'=>'error','message'=>$e->getMessage()],500);} }
if ($method==='POST' && $path==='/patients') {
  $name=trim($_POST['name']??''); $birth=trim($_POST['birth_date']??''); $phone=trim($_POST['phone']??''); $cell=trim($_POST['cellphone']??''); $email=trim($_POST['email']??'');
  $err=[]; if (mb_strlen($name)<3) $err[]='Nome deve ter ao menos 3 caracteres.'; if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) $err[]='E-mail inválido.'; if ($birth!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$birth)) $err[]='Data no formato YYYY-MM-DD.';
  if ($err){ $msg='<div class="alert error"><strong>Erro:</strong><ul><li>'.implode('</li><li>',array_map('h',$err)).'</li></ul></div>'; echo page_form($msg,compact('name','birth','phone','cell','email')); exit; }
  try { $pdo=Db::conn(); $st=$pdo->prepare('INSERT INTO patients (name, birth_date, phone, cellphone, email) VALUES (:n,:b,:p,:c,:e)'); $st->execute([':n'=>$name?:null,':b'=>$birth?:null,':p'=>$phone?:null,':c'=>$cell?:null,':e'=>$email?:null]); echo page_form('<div class="alert success">Paciente cadastrado com sucesso.</div>'); exit; } catch (Throwable $e){ echo page_form('<div class="alert error"><strong>Erro ao salvar:</strong> '.h($e->getMessage()).'</div>',compact('name','birth','phone','cell','email')); exit; }
}
if ($method==='GET' && $path==='/'){ echo page_form(); exit; }
http_response_code(404); header('Content-Type: text/plain; charset=utf-8'); echo "Not Found";
function page_form(string $flash='', array $old=[]): string{
  $name=h($old['name']??''); $birth=h($old['birth']??''); $phone=h($old['phone']??''); $cell=h($old['cell']??''); $email=h($old['email']??'');
  return <<<HTML
<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cadastro de Pacientes</title>
<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:2rem}.container{max-width:640px;margin:0 auto}h1{margin-bottom:.5rem}p.desc{color:#555;margin-top:0}form{display:grid;gap:12px;margin-top:16px}label{font-weight:600}input[type=text],input[type=date],input[type=email],input[type=tel]{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}button{padding:12px 16px;border:0;border-radius:8px;cursor:pointer}button.primary{background:#0d6efd;color:#fff}.alert{padding:12px 14px;border-radius:8px;margin:8px 0 4px}.alert.success{background:#e6f4ea;color:#1e7e34;border:1px solid #b7e1c1}.alert.error{background:#fdecea;color:#a11;border:1px solid #f5c2c7}small.hint{color:#666}.muted{color:#666;font-size:14px}.row{display:grid;gap:12px;grid-template-columns:1fr 1fr}</style></head>
<body><div class="container"><h1>Cadastro de Pacientes</h1><p class="desc">Preencha seus dados para contato e agendamento.</p>{$flash}
<form method="post" action="/patients" novalidate>
  <div><label for="name">Nome completo *</label><input type="text" id="name" name="name" value="{$name}" required></div>
  <div><label for="birth_date">Data de nascimento</label><input type="date" id="birth_date" name="birth_date" value="{$birth}" placeholder="YYYY-MM-DD"></div>
  <div class="row"><div><label for="phone">Telefone (fixo)</label><input type="tel" id="phone" name="phone" value="{$phone}"></div>
  <div><label for="cellphone">Celular</label><input type="tel" id="cellphone" name="cellphone" value="{$cell}"></div></div>
  <div><label for="email">E-mail</label><input type="email" id="email" name="email" value="{$email}" placeholder="voce@exemplo.com"></div>
  <div><button class="primary" type="submit">Enviar cadastro</button></div>
  <p class="muted"><small class="hint">Ao enviar, você concorda com o uso dos seus dados para contato e agendamento.</small></p>
</form><p class="muted">Endpoints: <code>/health</code> • <code>/db-check</code> • <code>POST /patients</code></p></div></body></html>
HTML;
}
