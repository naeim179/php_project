<?php
require_once 'helpers.php';
require_once 'db.php';

$user = require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Invalid complaint ID.');
}

$stmt = $pdo->prepare(
    'SELECT c.*, u.name AS student_name
     FROM complaints c
     JOIN users u ON u.id = c.user_id
     WHERE c.id = ?'
);
$stmt->execute([$id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    http_response_code(404);
    exit('Complaint not found.');
}

$isAdmin = $user['role'] === 'admin';

if (!$isAdmin && (int)$complaint['user_id'] !== $user['id']) {
    http_response_code(403);
    exit('Access denied.');
}

$error = '';
$statuses = ['pending', 'in_progress', 'resolved'];
$chosenStatus = $complaint['status'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) {
        http_response_code(403);
        exit('Admins only.');
    }

    verify_csrf();

    $chosenStatus = (string)($_POST['status'] ?? '');
    $message = trim((string)($_POST['message'] ?? ''));
    $statusChanged = $chosenStatus !== $complaint['status'];

    if (!in_array($chosenStatus, $statuses, true)) {
        $error = 'Invalid status.';
    } elseif (strlen($message) > 4000) {
        $error = 'Comment is too long.';
    } elseif (!$statusChanged && $message === '') {
        $error = 'Change status or add a comment first.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($statusChanged) {
                $update = $pdo->prepare(
                    'UPDATE complaints SET status = ? WHERE id = ?'
                );
                $update->execute([$chosenStatus, $id]);
            }

            if ($message !== '') {
                $reply = $pdo->prepare(
                    'INSERT INTO responses
                     (complaint_id, admin_id, message)
                     VALUES (?, ?, ?)'
                );
                $reply->execute([$id, $user['id'], $message]);
            }

            $pdo->commit();
            redirect('complaint.php?id=' . $id . '&saved=1');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            $error = 'Could not save. Please try again.';
        }
    }
}

$replyStmt = $pdo->prepare(
    'SELECT r.message, r.created_at, u.name AS admin_name
     FROM responses r
     JOIN users u ON u.id = r.admin_id
     WHERE r.complaint_id = ?
     ORDER BY r.id'
);
$replyStmt->execute([$id]);
$replies = $replyStmt->fetchAll();

page_top('Complaint #' . $id);
?>

<div class="eyebrow">
    <?= $isAdmin ? 'Administrator' : 'Student' ?>
</div>

<div class="split">
    <h1>Complaint #<?= (int)$id ?></h1>
    <a class="btn secondary"
       href="<?= $isAdmin ? 'admin.php' : 'student.php' ?>">
       Back to Dashboard
    </a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert success">
        Status and response saved successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['created'])): ?>
    <div class="alert success">Complaint submitted successfully.</div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
<?php endif; ?>

<div class="two-cols">
    <div>
        <section class="panel">
            <h2><?= h($complaint['title']) ?></h2>

            <p>
                Student: <?= h($complaint['student_name']) ?>
            </p>

            <p>
                Category: <?= h($complaint['category']) ?>
            </p>

            <h3>Description</h3>
            <p><?= nl2br(h($complaint['description'])) ?></p>
        </section>

        <section class="panel">
            <h2>Status & Administration Updates</h2>

            <p>
                Current Status:
                <span class="badge <?= h($complaint['status']) ?>">
                    <?= h(state_label($complaint['status'])) ?>
                </span>
            </p>

            <?php if (!$replies): ?>
                <p class="sub">No responses yet.</p>
            <?php endif; ?>

            <ul class="conversation">
                <?php foreach ($replies as $reply): ?>
                    <li>
                        <strong><?= h($reply['admin_name']) ?></strong>
                        <small><?= h($reply['created_at']) ?></small>
                        <div><?= nl2br(h($reply['message'])) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <?php if ($isAdmin): ?>
    <aside>
        <section class="panel">
            <h2>Update Complaint</h2>

            <form method="POST"
                  action="complaint.php?id=<?= (int)$id ?>">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= h(csrf_token()) ?>">

                <div class="form-field">
                    <label for="status">Complaint Status</label>
                    <select class="field" id="status" name="status">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= h($status) ?>"
                                <?= $chosenStatus === $status ? 'selected' : '' ?>>
                                <?= h(state_label($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="message">Response / Comment</label>
                    <textarea
                        class="field"
                        id="message"
                        name="message"
                        maxlength="4000"
                        placeholder="Write your response (optional)..."
                    ><?= h($message) ?></textarea>
                </div>

                <button class="btn" type="submit">
                    Save Update & Response
                </button>

            </form>
        </section>
    </aside>
    <?php endif; ?>
</div>

<?php page_bottom(); ?>
