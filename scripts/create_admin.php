<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require dirname(__DIR__) . '/db.php';

function prompt(string $label): string {
    echo $label;
    $value = fgets(STDIN);
    return $value === false ? '' : trim($value);
}

function generated_password(): string {
    return bin2hex(random_bytes(12)) . 'Aa1!';
}

$name = prompt('New admin name: ');
$email = strtolower(prompt('New admin email: '));

if ($name === '' || strlen($name) > 100) {
    fwrite(STDERR, "Name is required and must not exceed 100 characters.\n");
    exit(1);
}
if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Enter a valid email address.\n");
    exit(1);
}

$password = generated_password();
try {
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        fwrite(STDERR, "That email address is already registered. No account was changed.\n");
        exit(1);
    }
    error_log($e->getMessage());
    fwrite(STDERR, "Could not create the administrator.\n");
    exit(1);
}

echo "\nADMIN CREATED SUCCESSFULLY\nEmail: {$email}\nPassword: {$password}\n";
echo "Store the password privately. It will not be shown again.\n";
