<?php
require_once 'helpers.php';
require_once 'db.php';
$user = require_role('admin');
$statuses = ['pending', 'in_progress', 'resolved'];
$filter = (string)($_GET['status'] ?? 'all');
if ($filter !== 'all' && !in_array($filter, $statuses, true)) $filter = 'all';
$counts = $pdo->query("SELECT COUNT(*) total,
 SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
 SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) in_progress,
 SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) resolved FROM complaints")->fetch();
$sql = 'SELECT c.id,c.title,c.category,c.status,c.created_at,u.name student_name,u.email student_email
 FROM complaints c JOIN users u ON c.user_id=u.id';
if ($filter !== 'all') $sql .= ' WHERE c.status = ?';
$sql .= ' ORDER BY c.id DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($filter === 'all' ? [] : [$filter]);
$complaints = $stmt->fetchAll();
page_top('Admin Dashboard');
?>
<div class="eyebrow">Administration</div><h1>Complaint Management</h1><p class="sub">Review student submissions, update progress and respond to complaints.</p>
<div class="grid admin-metrics">
<div class="metric"><span>All Complaints</span><strong><?= (int)$counts['total'] ?></strong></div>
<div class="metric"><span>Pending</span><strong><?= (int)$counts['pending'] ?></strong></div>
<div class="metric"><span>In Progress</span><strong><?= (int)$counts['in_progress'] ?></strong></div>
<div class="metric"><span>Resolved</span><strong><?= (int)$counts['resolved'] ?></strong></div>
</div>
<section class="panel"><div class="panel-header"><h2>Student Complaints</h2>
<form class="filter" method="get" action="admin.php"><label for="status">Status</label><select class="field" id="status" name="status"><option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option><?php foreach ($statuses as $status): ?><option value="<?= h($status) ?>" <?= $filter === $status ? 'selected' : '' ?>><?= h(state_label($status)) ?></option><?php endforeach; ?></select><button class="btn secondary" type="submit">Filter</button></form>
</div><p class="table-hint">Swipe the table to see all columns.</p><div class="table-wrap"><table><thead><tr><th>ID</th><th>Complaint</th><th>Student</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($complaints as $c): ?><tr><td>#<?= (int)$c['id'] ?></td><td class="truncate"><?= h($c['title']) ?></td><td><?= h($c['student_name']) ?><div class="tiny"><?= h($c['student_email']) ?></div></td><td><?= h($c['category']) ?></td><td><span class="badge <?= h($c['status']) ?>"><?= h(state_label($c['status'])) ?></span></td><td><?= h($c['created_at']) ?></td><td><a class="link-action" href="complaint.php?id=<?= (int)$c['id'] ?>">Manage →</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php if (!$complaints): ?><p class="empty">No complaints found.</p><?php endif; ?></section>
<div class="note"><strong>Database relationship in action:</strong> This page uses <code>complaints.user_id = users.id</code> to show the student's name beside each complaint.</div>
<?php page_bottom(); ?>
