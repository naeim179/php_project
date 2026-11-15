<?php
require_once 'helpers.php';
require_once 'db.php';
$user = require_role('student');
$summaryStmt = $pdo->prepare("SELECT COUNT(*) total,
  SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
  SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) in_progress,
  SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) resolved
  FROM complaints WHERE user_id = ?");
$summaryStmt->execute([$user['id']]);
$counts = $summaryStmt->fetch();
$stmt = $pdo->prepare('SELECT id,title,category,status,created_at FROM complaints WHERE user_id=? ORDER BY id DESC LIMIT 100');
$stmt->execute([$user['id']]);
$complaints = $stmt->fetchAll();
page_top('Student Dashboard');
?>
<div class="split"><div><div class="eyebrow">Student workspace</div><h1>Hello, <?= h($user['name']) ?>!</h1><p class="sub">Your concerns, progress and replies in one place.</p></div><a class="btn" href="new_complaint.php">+ Submit Complaint</a></div>
<div class="grid">
  <div class="metric"><span>Total complaints</span><strong><?= (int)$counts['total'] ?></strong></div>
  <div class="metric"><span>Pending / In Progress</span><strong><?= (int)$counts['pending'] + (int)$counts['in_progress'] ?></strong></div>
  <div class="metric"><span>Resolved</span><strong><?= (int)$counts['resolved'] ?></strong></div>
</div>
<div class="panel"><div class="panel-header"><h2>My Complaint History</h2><span class="tiny">Latest 100 records</span></div><div class="table-wrap"><table>
<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($complaints as $c): ?>
<tr><td>#<?= (int)$c['id'] ?></td><td class="truncate"><?= h($c['title']) ?></td><td><?= h($c['category']) ?></td><td><span class="badge <?= h($c['status']) ?>"><?= h(state_label($c['status'])) ?></span></td><td><?= h($c['created_at']) ?></td><td><a class="link-action" href="complaint.php?id=<?= (int)$c['id'] ?>">View →</a></td></tr>
<?php endforeach; ?>
</tbody></table></div><?php if (!$complaints): ?><div class="empty">No complaints yet. <a href="new_complaint.php">Submit your first complaint.</a></div><?php endif; ?></div>
<?php page_bottom(); ?>
