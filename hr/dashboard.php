<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../includes/topbar.php';
require_role('hr', 'login.php');

$hr = current_hr();
$employees = load_company_employees($hr['id']);

$flash = $_SESSION['flash_new_account'] ?? null;
unset($_SESSION['flash_new_account']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR dashboard · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="topbar">
  <?php render_company_brand($hr); ?>
  <?php render_topbar_nav('hr', 'dashboard'); ?>
  <div class="topbar__spacer"></div>
  <div class="role-toggle">
    Signed in as <strong style="color:var(--lilac-700);"><?= htmlspecialchars($hr['name']) ?></strong> (HR)
    <form method="get" action="../logout.php" style="margin-left:4px;">
      <button type="submit">Sign out</button>
    </form>
  </div>
</header>

<main class="dash-shell">

  <?php if ($flash): ?>
    <div class="credentials-banner">
      <div class="glyph">✅</div>
      <div>
        <h4>Employee account created</h4>
        <p>Share these sign-in details with <?= htmlspecialchars($flash['name']) ?> — the password is shown once and can't be retrieved again.</p>
        <div class="cred-row">
          <div class="item"><span>Login ID</span><?= htmlspecialchars($flash['login_id']) ?></div>
          <div class="item"><span>Temporary password</span><?= htmlspecialchars($flash['temp_password']) ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="dash-head">
    <div>
      <h1>Employees</h1>
      <p><?= count($employees) ?> <?= count($employees) === 1 ? 'person' : 'people' ?> in the company</p>
    </div>
    <a href="create_employee.php" class="btn btn-primary">+ Create employee</a>
  </div>

  <div class="employee-list">
    <?php if (empty($employees)): ?>
      <div class="empty-dash">No employees yet — create the first one to get started.</div>
    <?php else: ?>
      <?php foreach ($employees as $id => $emp): ?>
        <a class="employee-row" href="employee_profile.php?id=<?= urlencode($id) ?>">
          <div class="avatar-sm"><?= htmlspecialchars(initials($emp['name'])) ?></div>
          <div class="info">
            <div class="name"><?= htmlspecialchars($emp['name']) ?></div>
            <div class="sub"><?= htmlspecialchars($emp['department']) ?> · <?= htmlspecialchars($emp['login_id']) ?></div>
          </div>
          <span class="status-pill <?= empty($emp['password_hash']) ? 'invited' : 'active' ?>">
            <?= empty($emp['password_hash']) ? 'Invited' : 'Active' ?>
          </span>
          <div class="wage">
            <span class="label">Month wage</span>
            ₹<?= number_format((float) $emp['salary']['month_wage']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>
</body>
</html>
