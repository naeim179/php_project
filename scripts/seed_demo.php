<?php
// Local demonstration helper. Does not run in a browser.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}
require dirname(__DIR__) . '/db.php';

function new_password(): string {
    return bin2hex(random_bytes(7)) . 'Aa1!';
}
function create_demo_user(PDO $pdo, string $name, string $email, string $role): void {
    $pw = new_password();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        // For the dedicated demo accounts ONLY: reruns reset passwords.
        $stmt = $pdo->prepare('UPDATE users SET name=?, password=?, role=? WHERE id=?');
        $stmt->execute([$name, password_hash($pw, PASSWORD_DEFAULT), $role, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
        $stmt->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role]);
    }
    echo "Role: $role\nEmail: $email\nPassword: $pw\n\n";
}

echo "LOCAL DEMO ACCOUNTS — copy both passwords now.\n\n";
create_demo_user($pdo, 'Demo Student', 'student.demo@example.test', 'student');
create_demo_user($pdo, 'Demo Administrator', 'admin.demo@example.test', 'admin');
echo "Important: Demo-only accounts. Remove/disable them before deploying.\n";
