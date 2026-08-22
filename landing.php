<?php
/**
 * WorkforceID entry point.
 * Asks "Who are you?" and routes into the profile app with the matching
 * role — the same session role that already gates the Salary Info tab
 * in index.php. Real login/signup can replace the two links below later;
 * for now they set the role directly so the rest of the app is reachable.
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WorkforceID · Who are you?</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="landing-body">

  <div class="landing">
    <div class="landing__mark">
      <span class="mark"></span> Workforce<span class="accent">ID</span>
    </div>
    <p class="landing__tagline">Secure workforce identity &amp; salary management</p>

    <h1 class="landing__question">Who are you?</h1>

    <div class="role-grid">
      <a class="role-card" href="hr/login.php">
        <div class="glyph">🗂️</div>
        <h2>HR</h2>
        <p>Manage employee profiles, create employee accounts, and define salary structures and PF &amp; tax settings.</p>
        <span class="cta">Continue as HR <span class="arrow">→</span></span>
      </a>

      <a class="role-card" href="employee/login.php">
        <div class="glyph">🙋</div>
        <h2>Employee</h2>
        <p>View your own profile, private info and security settings. Salary info is managed by HR.</p>
        <span class="cta">Continue as Employee <span class="arrow">→</span></span>
      </a>
    </div>
  </div>

</body>
</html>
