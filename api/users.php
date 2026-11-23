<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$method = api_method(['GET', 'PATCH']);
$admin = api_admin();

if ($method === 'GET') {
    $search = trim((string)($_GET['q'] ?? ''));
    if (strlen($search) > 100) api_error('Search text is too long.');
    $sql = 'SELECT id,name,email,role,created_at FROM users';
    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE name LIKE ? OR email LIKE ?';
        $term = '%' . $search . '%';
        $params = [$term, $term];
    }
    $sql .= " ORDER BY CASE WHEN role='admin' THEN 0 ELSE 1 END,id DESC LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    api_response(['success' => true, 'users' => $users, 'count' => count($users)]);
}

api_verify_csrf();
$data = api_json_body();
if (array_key_exists('role', $data)) {
    api_error('Roles cannot be modified through this endpoint.', 403);
}
$id = api_positive_id($data['id'] ?? $_GET['id'] ?? null);
$name = api_text($data['name'] ?? null, 'Name', 1, 100);
$email = api_text($data['email'] ?? null, 'Email', 3, 255);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) api_error('Enter a valid email address.');

try {
    $stmt = $pdo->prepare('UPDATE users SET name=?,email=? WHERE id=?');
    $stmt->execute([$name, $email, $id]);
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT 1 FROM users WHERE id=?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) api_error('User not found.', 404);
    }
    if ($id === $admin['id']) $_SESSION['name'] = $name;
    api_response(['success' => true, 'message' => 'User information updated.']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') api_error('This email address is already in use.', 409);
    error_log($e->getMessage());
    api_error('Could not update the user.', 500);
}
