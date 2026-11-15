<?php
require_once 'helpers.php';
page_top('Use Case Diagram');
?>
<div class="eyebrow">Phase 1 → Live implementation</div><h1>From the diagram to real features</h1><p class="sub">The same Student and Administrator activities are implemented in the application's pages.</p>
<div class="panel"><img class="diagram-img" src="assets/use_case.png" alt="Use Case Diagram for the University Complaint Management System"></div>
<div class="panel"><h2>Real database relationships</h2>
<div class="table-wrap"><table><thead><tr><th>Relationship</th><th>Meaning</th><th>Where to see it</th></tr></thead><tbody>
<tr><td><code>complaints.user_id → users.id</code></td><td>One student can submit many complaints.</td><td>Student Dashboard + Admin Dashboard</td></tr>
<tr><td><code>responses.complaint_id → complaints.id</code></td><td>A complaint can have many responses.</td><td>Complaint details</td></tr>
<tr><td><code>responses.admin_id → users.id</code></td><td>Each response references the user who wrote it.</td><td>Administrator Responses list</td></tr>
</tbody></table></div></div>
<?php page_bottom(); ?>
