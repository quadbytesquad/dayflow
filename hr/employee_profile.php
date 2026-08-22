<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../includes/profile_view.php';
require_role('hr', 'login.php');

$hr = current_hr();
$id = $_GET['id'] ?? '';
$employees = load_employees();

if ($id === '' || !isset($employees[$id])) {
    http_response_code(404);
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not found</title>
    <link rel="stylesheet" href="../assets/style.css"></head>
    <body class="auth-body">
      <div class="auth-card">
        <h1>Employee not found</h1>
        <p class="auth-sub">This employee doesn't exist or may have been removed.</p>
        <a href="dashboard.php" class="btn btn-primary btn-block">Back to Employees</a>
      </div>
    </body></html>
    <?php
    exit;
}

$employee = $employees[$id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($employee['name']) ?> · WorkforceID</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
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

<main class="page">
  <a href="dashboard.php" class="auth-back" style="margin-bottom:14px;display:inline-flex;">← Employees</a>
  <p class="page__eyebrow">Employee Profile</p>
  <?php render_profile_card($employee, $id, true); ?>
</main>

<script src="../assets/script.js"></script>
</body>
</html>
