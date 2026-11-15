<?php
require_once 'helpers.php';
$user = current_user();
if ($user !== null) {
    redirect($user['role'] === 'admin' ? 'admin.php' : 'student.php');
}
page_top('Home');
?>
<section class="hero">
    <div class="eyebrow">A better student experience</div>
    <h1>Every student concern deserves to be heard.</h1>
    <p class="sub">Submit university complaints, follow their progress, and receive responses from administrators — all in one place.</p>
    <div class="actions"><a class="btn" href="register.php">Create student account</a><a class="btn outline" href="login.php">Log in</a></div>
</section>
<section class="features">
    <article><strong>01 · Submit</strong><p>Write a complaint and choose the relevant category.</p></article>
    <article><strong>02 · Track</strong><p>Follow its status: Pending, In Progress or Resolved.</p></article>
    <article><strong>03 · Respond</strong><p>Read official responses from university administrators.</p></article>
</section>
<div class="panel"><h2>From diagram to application</h2><p class="sub">Explore how the Student and Administrator use cases are implemented in the working system.</p><a class="link-action" href="diagram.php">View Use Case Diagram →</a></div>
<?php page_bottom(); ?>
