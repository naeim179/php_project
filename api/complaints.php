<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$method = api_method(['GET', 'PATCH', 'DELETE']);
$user = api_user();
$idValue = $_GET['id'] ?? null;
$id = $idValue === null || $idValue === '' ? null : api_positive_id($idValue);

if ($method === 'GET') {
    $where = [];
    $params = [];

    if (!is_admin($user)) {
        $where[] = 'c.user_id = ?';
        $params[] = $user['id'];
    }
    if ($id !== null) {
        $where[] = 'c.id = ?';
        $params[] = $id;
    }

    $status = trim((string)($_GET['status'] ?? ''));
    $statuses = ['pending', 'in_progress', 'resolved'];
    if ($status !== '' && $status !== 'all') {
        if (!in_array($status, $statuses, true)) api_error('Invalid status filter.');
        $where[] = 'c.status = ?';
        $params[] = $status;
    }

    $search = trim((string)($_GET['q'] ?? ''));
    if ($search !== '') {
        if (strlen($search) > 100) api_error('Search text is too long.');
        $where[] = '(c.title LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR c.status LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term);
    }

    $sql = 'SELECT c.id,c.user_id,c.title,c.category,c.description,c.status,c.created_at,
                   u.name AS student_name,u.email AS student_email,
                   (SELECT COUNT(*) FROM responses r WHERE r.complaint_id=c.id) AS response_count
            FROM complaints c JOIN users u ON u.id=c.user_id';
    if ($where !== []) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY c.id DESC LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if ($id !== null) {
        if (!$rows) api_error('Complaint not found.', 404);
        api_response(['success' => true, 'complaint' => $rows[0]]);
    }
    if (is_admin($user)) {
        $summary = $pdo->query("SELECT COUNT(*) total,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
            SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) in_progress,
            SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) resolved FROM complaints")->fetch();
    } else {
        $summaryStmt = $pdo->prepare("SELECT COUNT(*) total,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
            SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) in_progress,
            SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) resolved FROM complaints WHERE user_id=?");
        $summaryStmt->execute([$user['id']]);
        $summary = $summaryStmt->fetch();
    }
    api_response(['success' => true, 'complaints' => $rows, 'count' => count($rows), 'summary' => $summary]);
}

$admin = api_admin();
api_verify_csrf();
if ($id === null) api_error('Complaint ID is required.');

if ($method === 'PATCH') {
    $data = api_json_body();
    $title = api_text($data['title'] ?? null, 'Title', 1, 200);
    $category = api_text($data['category'] ?? null, 'Category', 1, 100);
    $description = api_text($data['description'] ?? null, 'Description', 10, 5000);

    $stmt = $pdo->prepare('UPDATE complaints SET title=?, category=?, description=? WHERE id=?');
    $stmt->execute([$title, $category, $description, $id]);
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT 1 FROM complaints WHERE id=?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) api_error('Complaint not found.', 404);
    }
    api_response(['success' => true, 'message' => 'Complaint updated.']);
}

try {
    $pdo->beginTransaction();
    $exists = $pdo->prepare('SELECT id FROM complaints WHERE id=? FOR UPDATE');
    $exists->execute([$id]);
    if (!$exists->fetchColumn()) {
        $pdo->rollBack();
        api_error('Complaint not found.', 404);
    }
    $pdo->prepare('DELETE FROM responses WHERE complaint_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM complaints WHERE id=?')->execute([$id]);
    $pdo->commit();
    api_response(['success' => true, 'message' => 'Complaint and its responses were deleted.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    api_error('Could not delete the complaint.', 500);
}
