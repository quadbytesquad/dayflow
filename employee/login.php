<?php
require_once __DIR__ . '/../lib/auth.php';

if (current_role() === 'employee') {
    header('Location: profile.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = trim($_POST['login_id'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($loginId === '' || $password === '') {
        $error = 'Enter your login ID and password.';
    } else {
        $id = attempt_employee_login($loginId, $password);
        if ($id !== null) {
            header('Location: profile.php');
            exit;
        }
        $error = 'That login ID and password don\'t match our records. Ask HR if you haven\'t received your login yet.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee sign in · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <a href="../landing.php" class="auth-back">← Who are you?</a>
    <div class="auth-glyph">🙋</div>
    <h1>Sign in</h1>
    <p class="auth-sub">Use the login ID and password HR gave you.</p>

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
      <strong>Demo credentials</strong> — login ID <code>ananya.sharma</code>, password <code>Welcome123!</code>
    </div>
  </div>
</body>
</html>
