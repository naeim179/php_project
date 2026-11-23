<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$required = ['pdo_mysql'];
$missing = array_values(array_filter($required, static fn(string $extension): bool => !extension_loaded($extension)));
if ($missing !== []) {
    fwrite(STDERR, 'Missing PHP extensions: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

require dirname(__DIR__) . '/db.php';

$expectedColumns = [
    'users' => ['id', 'name', 'email', 'password', 'role', 'created_at'],
    'complaints' => ['id', 'user_id', 'title', 'category', 'description', 'status', 'created_at'],
    'responses' => ['id', 'complaint_id', 'admin_id', 'message', 'created_at'],
];
$schema = $pdo->query('SELECT DATABASE()')->fetchColumn();
if (!is_string($schema) || $schema === '') {
    fwrite(STDERR, "No database selected.\n");
    exit(1);
}

$columns = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
$problems = [];
foreach ($expectedColumns as $table => $expected) {
    $columns->execute([$schema, $table]);
    $found = $columns->fetchAll(PDO::FETCH_COLUMN);
    $missingColumns = array_diff($expected, $found);
    if ($missingColumns !== []) {
        $problems[] = $table . ': missing ' . implode(', ', $missingColumns);
    }
}

$foreignKeys = $pdo->prepare('SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL');
$foreignKeys->execute([$schema]);
$foundKeys = [];
foreach ($foreignKeys as $key) {
    $foundKeys[$key['TABLE_NAME'] . '.' . $key['COLUMN_NAME']] = $key['REFERENCED_TABLE_NAME'];
}
foreach (['complaints.user_id' => 'users', 'responses.complaint_id' => 'complaints', 'responses.admin_id' => 'users'] as $column => $target) {
    if (($foundKeys[$column] ?? null) !== $target) {
        $problems[] = $column . ': expected a foreign key to ' . $target;
    }
}

if ($problems !== []) {
    fwrite(STDERR, "Database needs attention:\n- " . implode("\n- ", $problems) . PHP_EOL);
    exit(1);
}

echo "OK: PHP extensions, database connection, tables, and three foreign keys.\n";
