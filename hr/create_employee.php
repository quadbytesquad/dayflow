<?php
require_once __DIR__ . '/../lib/auth.php';
require_role('hr', 'login.php');

$hr = current_hr();
$error = null;
$values = ['name' => '', 'department' => '', 'manager' => '', 'location' => '', 'email' => '', 'mobile' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if ($values['name'] === '') {
        $error = 'Employee name is required.';
    } elseif ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'That email address doesn\'t look right.';
    } else {
        $employees = load_employees();

        $id = generate_employee_id($employees);
        $joinYear = date('Y');
        $loginId = generate_login_id($values['name'], $hr['company_initials'] ?? 'CO', $joinYear, $employees);
        $tempPassword = generate_temp_password();

        $employees[$id] = [
            'name' => $values['name'],
            'department' => $values['department'] ?: '—',
            'manager' => $values['manager'] ?: '—',
            'location' => $values['location'] ?: '—',
            'email' => $values['email'] ?: '—',
            'mobile' => $values['mobile'] ?: '—',
            'login_id' => $loginId,
            'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'date_of_joining' => date('Y-m-d'),
            'status' => 'active',
            'salary' => default_salary_template(),
        ];

        if (save_employees($employees)) {
            $_SESSION['flash_new_account'] = [
                'name' => $values['name'],
                'login_id' => $loginId,
                'temp_password' => $tempPassword,
            ];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Could not save the new employee — check that the data folder is writable.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create employee · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="topbar">
  <a href="../landing.php" class="topbar__logo" style="text-decoration:none;"><span class="mark"></span> Company Logo</a>
  <nav class="topbar__nav">
    <a href="dashboard.php">Employees</a>
    <a href="#">Attendance</a>
    <a href="#">Time Off</a>
  </nav>
  <div class="topbar__spacer"></div>
  <div class="role-toggle">
    Signed in as <strong style="color:var(--lilac-700);"><?= htmlspecialchars($hr['name']) ?></strong> (HR)
    <form method="get" action="../logout.php" style="margin-left:4px;">
      <button type="submit">Sign out</button>
    </form>
  </div>
</header>

<main class="dash-shell" style="max-width:600px;">
  <a href="dashboard.php" class="auth-back">← Employees</a>

  <div class="dash-head" style="margin-bottom:20px;">
    <div>
      <h1>Create employee</h1>
      <p>We'll generate a login ID and a one-time password for them to sign in with.</p>
    </div>
  </div>

  <div class="auth-card" style="max-width:none;">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-field">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($values['name']) ?>">
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="department">Department</label>
          <input type="text" id="department" name="department" value="<?= htmlspecialchars($values['department']) ?>" placeholder="e.g. Product Design">
        </div>
        <div class="form-field">
          <label for="manager">Manager</label>
          <input type="text" id="manager" name="manager" value="<?= htmlspecialchars($values['manager']) ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email']) ?>">
        </div>
        <div class="form-field">
          <label for="mobile">Mobile</label>
          <input type="text" id="mobile" name="mobile" value="<?= htmlspecialchars($values['mobile']) ?>">
        </div>
      </div>

      <div class="form-field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= htmlspecialchars($values['location']) ?>">
      </div>

      <p class="form-field .hint" style="font-size:0.78rem;color:var(--muted);">
        Salary starts at ₹0 with default component percentages — set the real wage from the employee's Salary Info tab after creating the account.
      </p>

      <button type="submit" class="btn btn-primary btn-block">Create employee &amp; generate login</button>
    </form>
  </div>
</main>
</body>
</html>
