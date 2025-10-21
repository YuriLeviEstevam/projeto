<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
use App\Auth;

Auth::logout();
header('Location: /admin/login.php');
exit;
