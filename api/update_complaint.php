<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_method(['PATCH']);
$admin = api_admin();
api_verify_csrf();
$data = api_json_body();
$id = api_positive_id($data['id'] ?? $_GET['id'] ?? null);
$status = (string)($data['status'] ?? '');
$message = trim((string)($data['message'] ?? ''));
$statuses = ['pending', 'in_progress', 'resolved'];

if (!in_array($status, $statuses, true)) api_error('Invalid complaint status.');
if (strlen($message) > 4000) api_error('Response is too long.');

try {
    $pdo->beginTransaction();
    $find = $pdo->prepare('SELECT status FROM complaints WHERE id=? FOR UPDATE');
    $find->execute([$id]);
    $oldStatus = $find->fetchColumn();
    if ($oldStatus === false) {
        $pdo->rollBack();
        api_error('Complaint not found.', 404);
    }
    if ($oldStatus === $status && $message === '') {
        $pdo->rollBack();
        api_error('Change the status or add a response first.');
    }
    if ($oldStatus !== $status) {
        $pdo->prepare('UPDATE complaints SET status=? WHERE id=?')->execute([$status, $id]);
    }
    if ($message !== '') {
        $pdo->prepare('INSERT INTO responses (complaint_id,admin_id,message) VALUES (?,?,?)')
            ->execute([$id, $admin['id'], $message]);
    }
    $pdo->commit();
    api_response(['success' => true, 'message' => 'Status and response updated.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    api_error('Could not update the complaint.', 500);
}
