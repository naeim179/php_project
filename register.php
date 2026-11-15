<?php
require_once 'helpers.php';
require_once 'db.php';
if (current_user() !== null) redirect('index.php');
$error = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || strlen($name) > 100) {
        $error = 'Enter a name (maximum 100 bytes).';
    } elseif (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'student']);
            redirect('login.php?registered=1');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') $error = 'This email is already registered.';
            else { error_log($e->getMessage()); $error = 'Registration failed. Try again.'; }
        }
    }
}
page_top('Create account');
?>
<section class="form-wrap"><div class="eyebrow">Student access</div><h1>Create student account</h1><p class="sub">Your account gives you access to complaint tracking and history.</p>
<div class="panel">
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post" action="register.php">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="form-field"><label for="name">Full name</label><input class="field" id="name" type="text" name="name" maxlength="100" required value="<?= h($name) ?>" autocomplete="name"></div>
    <div class="form-field"><label for="email">Email address</label><input class="field" id="email" type="email" name="email" maxlength="255" required value="<?= h($email) ?>" autocomplete="email"></div>
    <div class="form-field"><label for="password">Password</label><input class="field" id="password" type="password" name="password" minlength="8" required autocomplete="new-password"><div class="help">Use 8 or more characters.</div></div>
    <button class="btn" type="submit">Create account</button>
</form><p class="tiny">Already registered? <a href="login.php">Log in</a></p></div></section>
<?php page_bottom(); ?>
