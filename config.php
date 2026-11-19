<?php
// FoodOrder — баптаулар және көмекші функциялар
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'foodorder');
define('DB_USER', 'root');
define('DB_PASS', '');          // өз құпиясөзіңізді жазыңыз

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $st = db()->prepare('SELECT id,name,email FROM users WHERE id=?');
    $st->execute([$_SESSION['uid']]);
    return $st->fetch() ?: null;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_ok(?string $t): bool {
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function hhmm(string $t): string { return substr($t, 0, 5); }

const TYPE_LABELS = ['table' => 'Стол', 'cabin' => 'Кабина', 'vip' => 'VIP зал'];
const CAT_LABELS  = ['food' => 'Тағамдар', 'drink' => 'Сусындар', 'dessert' => 'Тәттілер'];
