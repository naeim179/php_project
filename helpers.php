<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}
function verify_csrf(): void {
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
        !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(400);
        exit('Invalid form token. Reload the page and try again.');
    }
}
function current_user(): ?array {
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) return null;
    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => (string)($_SESSION['name'] ?? ''),
        'role' => (string)$_SESSION['role'],
    ];
}
function require_login(): array {
    $user = current_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}
function require_role(string $role): array {
    $user = require_login();
    if ($user['role'] !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }
    return $user;
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function state_label(string $status): string {
    return match ($status) {
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        default => 'Unknown',
    };
}
function page_top(string $title): void {
    $user = current_user();
    $dash = $user && $user['role'] === 'admin' ? 'admin.php' : 'student.php';
    $title = h($title);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . $title . ' | CampusVoice</title>';
    echo '<link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="site-header"><div class="shell header-inner">';
    echo '<a class="logo" href="index.php"><span class="logo-mark">CV</span><span>CampusVoice<small>University Complaint System</small></span></a>';
    echo '<nav class="nav">';
    if ($user) {
        echo '<a href="' . $dash . '">Dashboard</a>';
        if ($user['role'] === 'student') echo '<a href="new_complaint.php">New Complaint</a>';
        echo '<a href="diagram.php">Use Case Diagram</a>';
        echo '<span class="user-pill">' . h($user['name']) . '</span>';
        echo '<form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '"><button class="nav-logout" type="submit">Log out</button></form>';
    } else {
        echo '<a href="login.php">Log in</a><a class="nav-join" href="register.php">Create account</a>';
    }
    echo '</nav></div></header><main class="shell main">';
}
function page_bottom(): void {
    echo '</main><footer class="site-footer"><div class="shell">CampusVoice · PHP + MariaDB · University Project Demo</div></footer></body></html>';
}
