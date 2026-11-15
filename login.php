<?php
require_once 'helpers.php';
require_once 'db.php';
if (current_user() !== null) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirect('index.php');
        }
    }
    $error = 'Invalid email or password.';
}
page_top('Login');
?>
<section class="form-wrap"><div class="eyebrow">Secure portal</div><h1>Welcome back</h1><p class="sub">Sign in to follow and manage university complaints.</p>
<div class="panel">
<?php if (isset($_GET['registered'])): ?><div class="alert success">Account created! You can log in now.</div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="form-field"><label for="email">Email</label><input class="field" id="email" type="email" name="email" maxlength="255" required autocomplete="username"></div>
    <div class="form-field"><label for="password">Password</label><input class="field" id="password" type="password" name="password" required autocomplete="current-password"></div>
    <button class="btn" type="submit">Log in</button>
</form><p class="tiny">New student? <a href="register.php">Create an account</a></p></div></section>
<?php page_bottom(); ?>
