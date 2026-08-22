<?php
require_once __DIR__ . '/../lib/auth.php';

if (current_role() === 'hr') {
    header('Location: dashboard.php');
    exit;
}

$error = null;
$values = ['company_name' => '', 'name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? '');
    }
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($values['company_name'] === '' || $values['name'] === '' || $values['email'] === '') {
        $error = 'Company name, your name and email are required.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'That email address doesn\'t look right.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords don\'t match.';
    } else {
        $hrAccounts = load_hr_accounts();

        $emailTaken = false;
        foreach ($hrAccounts as $acc) {
            if (strcasecmp($acc['login_id'], $values['email']) === 0) {
                $emailTaken = true;
                break;
            }
        }

        if ($emailTaken) {
            $error = 'An account with that email already exists.';
        } else {
            $logoPath = null;
            if (!empty($_FILES['logo']['name']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $logoPath = save_uploaded_logo($_FILES['logo'], $values['company_name']);
                if ($logoPath === null) {
                    $error = 'Logo must be a PNG, JPG, WebP or SVG image under 2MB.';
                }
            }

            if ($error === null) {
                $newId = 'hr-' . str_pad((string) (count($hrAccounts) + 1), 3, '0', STR_PAD_LEFT);
                $hrAccounts[] = [
                    'id' => $newId,
                    'company_name' => $values['company_name'],
                    'company_initials' => company_initials($values['company_name']),
                    'name' => $values['name'],
                    'email' => $values['email'],
                    'phone' => $values['phone'] ?: '—',
                    'login_id' => $values['email'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'logo_path' => $logoPath,
                ];

                if (save_hr_accounts($hrAccounts)) {
                    attempt_hr_login($values['email'], $password);
                    header('Location: dashboard.php');
                    exit;
                }
                $error = 'Could not save the new account — check that the data folder is writable.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create your company · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card" style="max-width:440px;">
    <a href="../landing.php" class="auth-back">← Who are you?</a>
    <div class="auth-glyph">🏢</div>
    <h1>Create your company</h1>
    <p class="auth-sub">Sign up as HR/Admin to manage your team on WorkforceID.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <div class="form-field">
        <label for="company_name">Company name</label>
        <div class="field-with-action">
          <input type="text" id="company_name" name="company_name" required
                 value="<?= htmlspecialchars($values['company_name']) ?>" placeholder="e.g. Odoo India">
          <button type="button" class="upload-btn" id="logo-trigger" title="Upload company logo">⬆</button>
          <input type="file" name="logo" id="logo-input" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display:none;">
        </div>
        <div class="logo-filename" id="logo-filename"></div>
      </div>

      <div class="form-field">
        <label for="name">Your name</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($values['name']) ?>">
      </div>

      <div class="form-field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="username" required value="<?= htmlspecialchars($values['email']) ?>">
      </div>

      <div class="form-field">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($values['phone']) ?>">
      </div>

      <div class="form-field">
        <label for="password">Password</label>
        <div class="pw-field">
          <input type="password" id="password" name="password" autocomplete="new-password" required minlength="8">
          <button type="button" class="pw-toggle" data-target="password" title="Show password">👁</button>
        </div>
        <div class="hint">At least 8 characters.</div>
      </div>

      <div class="form-field">
        <label for="confirm_password">Confirm password</label>
        <div class="pw-field">
          <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required minlength="8">
          <button type="button" class="pw-toggle" data-target="confirm_password" title="Show password">👁</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
    </form>

    <div class="auth-switch">Already have an account? <a href="login.php">Sign In</a></div>
  </div>

  <script>
    var logoTrigger = document.getElementById('logo-trigger');
    var logoInput = document.getElementById('logo-input');
    var logoFilename = document.getElementById('logo-filename');
    logoTrigger.addEventListener('click', function () { logoInput.click(); });
    logoInput.addEventListener('change', function () {
      if (logoInput.files && logoInput.files[0]) {
        logoFilename.textContent = logoInput.files[0].name;
        logoTrigger.classList.add('has-logo');
        logoTrigger.textContent = '✓';
      } else {
        logoFilename.textContent = '';
        logoTrigger.classList.remove('has-logo');
        logoTrigger.textContent = '⬆';
      }
    });

    document.querySelectorAll('.pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-target'));
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        btn.textContent = showing ? '👁' : '🙈';
        btn.title = showing ? 'Show password' : 'Hide password';
      });
    });
  </script>
</body>
</html>
