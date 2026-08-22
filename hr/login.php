<?php
require_once __DIR__ . '/../lib/auth.php';

if (current_role() === 'hr') {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = trim($_POST['login_id'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($loginId === '' || $password === '') {
        $error = 'Enter your login ID and password.';
    } elseif (attempt_hr_login($loginId, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'That login ID and password don\'t match our records.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR sign in · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <a href="../landing.php" class="auth-back">← Who are you?</a>
    <div class="auth-glyph">🗂️</div>
    <h1>Sign in as HR</h1>
    <p class="auth-sub">Manage employee profiles and salary information.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-field">
        <label for="login_id">Login ID</label>
        <input type="text" id="login_id" name="login_id" autocomplete="username"
               value="<?= htmlspecialchars($_POST['login_id'] ?? '') ?>" required>
      </div>
      <div class="form-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>

    <div class="demo-creds">
      <strong>Demo credentials</strong> — login ID <code>priya.nair</code>, password <code>HrDemo123!</code>
    </div>

    <div class="auth-switch">New company? <a href="signup.php">Sign Up</a></div>
  </div>
</body>
</html>
