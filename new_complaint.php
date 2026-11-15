<?php
require_once 'helpers.php';
require_once 'db.php';
$user = require_role('student');
$categories = ['IT', 'Academic', 'Facilities', 'Other'];
$error = '';
$title = '';
$category = 'IT';
$description = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $category = (string)($_POST['category'] ?? '');
    $description = trim((string)($_POST['description'] ?? ''));
    if ($title === '' || strlen($title) > 200) $error = 'Title is required (maximum 200 bytes).';
    elseif (!in_array($category, $categories, true)) $error = 'Choose a valid category.';
    elseif (strlen($description) < 10 || strlen($description) > 5000) $error = 'Description must be 10–5000 bytes.';
    else {
        $stmt = $pdo->prepare('INSERT INTO complaints (user_id,title,category,description) VALUES (?,?,?,?)');
        $stmt->execute([$user['id'], $title, $category, $description]);
        redirect('complaint.php?id=' . (int)$pdo->lastInsertId() . '&created=1');
    }
}
page_top('Submit Complaint');
?>
<div class="eyebrow">Student · New request</div><h1>Submit a Complaint</h1><p class="sub">Explain the issue clearly so the administration can review it.</p>
<section class="form-wrap panel">
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post" action="new_complaint.php">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<div class="form-field"><label for="title">Complaint title</label><input class="field" id="title" name="title" maxlength="200" required placeholder="e.g. Wi-Fi unavailable in laboratory" value="<?= h($title) ?>"></div>
<div class="form-field"><label for="category">Category</label><select class="field" id="category" name="category" required><?php foreach ($categories as $cat): ?><option value="<?= h($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= h($cat) ?></option><?php endforeach; ?></select></div>
<div class="form-field"><label for="description">Description</label><textarea class="field" id="description" name="description" required minlength="10" maxlength="5000" placeholder="Describe what happened, where, and when..."><?= h($description) ?></textarea></div>
<div class="actions"><button class="btn" type="submit">Submit Complaint</button><a class="btn secondary" href="student.php">Cancel</a></div>
</form></section>
<?php page_bottom(); ?>
