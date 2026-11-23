<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

function api_response(array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_error(string $message, int $status = 400, array $details = []): never {
    api_response(['success' => false, 'error' => $message] + $details, $status);
}

function api_user(): array {
    $user = current_user();
    if ($user === null) api_error('Authentication required.', 401);
    return $user;
}

function api_admin(): array {
    $user = api_user();
    if (!is_admin($user)) api_error('Administrator access required.', 403);
    return $user;
}

function api_method(array $allowed): string {
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        api_error('Method not allowed.', 405);
    }
    return $method;
}

function api_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    try {
        $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_error('Request body must contain valid JSON.');
    }
    if (!is_array($data)) api_error('JSON request body must be an object.');
    return $data;
}

function api_verify_csrf(): void {
    if (!verify_csrf_value(request_csrf_token())) {
        api_error('Invalid security token. Reload the page and try again.', 403);
    }
}

function api_positive_id(mixed $value): int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) api_error('A valid record ID is required.');
    return (int)$id;
}

function api_text(mixed $value, string $field, int $min, int $max): string {
    if (!is_string($value)) api_error("{$field} must be text.");
    $value = trim($value);
    $length = strlen($value);
    if ($length < $min || $length > $max) {
        api_error("{$field} must be between {$min} and {$max} characters.");
    }
    return $value;
}
