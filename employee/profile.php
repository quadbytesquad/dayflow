<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../includes/profile_view.php';
require_once __DIR__ . '/../includes/topbar.php';

require_role('employee', 'login.php');

$id = current_employee_id();
$employees = load_employees();

if ($id === null || !isset($employees[$id])) {
    logout_all();
    header('Location: login.php');
    exit;
}

$employee = $employees[$id];
$company = find_hr_account($employee['hr_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile · <?= htmlspecialchars($employee['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="topbar">
  <?php render_company_brand($company); ?>
  <?php render_topbar_nav('employee', 'profile'); ?>
  <div class="topbar__spacer"></div>
  <div class="role-toggle">
    Signed in as
    <strong style="color:var(--lilac-700);"><?= htmlspecialchars($employee['name']) ?></strong>
    <form method="get" action="../logout.php" style="margin-left:4px;">
      <button type="submit">Sign out</button>
    </form>
  </div>
</header>

<main class="page">
  <p class="page__eyebrow">My Profile</p>
  <?php render_profile_card($employee, $id, false); ?>
</main>

<script src="../assets/script.js"></script>
</body>
</html>
