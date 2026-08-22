<?php
/**
 * Renders the "Company Logo" link in the topbar using the signed-in
 * company's actual name and uploaded logo (falls back to the default
 * mark + "WorkforceID" if no company/logo is available).
 *
 * @param array|null $company Expects 'company_name' and 'logo_path' keys
 *                             (the $_SESSION['hr'] array, or an hr_accounts row).
 */
function render_company_brand(?array $company): void
{
    $name = htmlspecialchars($company['company_name'] ?? 'WorkforceID');
    $logo = $company['logo_path'] ?? null;
    ?>
    <a href="../landing.php" class="topbar__logo" style="text-decoration:none;">
      <?php if ($logo): ?>
        <img src="../<?= htmlspecialchars($logo) ?>" alt="<?= $name ?>" class="topbar__logo-img">
      <?php else: ?>
        <span class="mark"></span>
      <?php endif; ?>
      <?= $name ?>
    </a>
    <?php
}

/**
 * Renders the shared topbar navigation for a logged-in page, so HR and
 * Employee areas stay visually connected (same structure, same styling)
 * while remaining functionally separate (each role only ever sees links
 * into its own section — HR never links into an employee page and vice
 * versa). Attendance / Time Off are not built yet, so they render as
 * disabled "coming soon" links instead of dead links to missing pages.
 *
 * @param string $role   'hr' or 'employee' — controls which link set is shown.
 * @param string $active Key of the item that should be marked active, e.g. 'dashboard'.
 */
function render_topbar_nav(string $role, string $active): void
{
    if ($role === 'hr') {
        $items = [
            'dashboard' => ['label' => 'Employees', 'href' => 'dashboard.php'],
            'attendance' => ['label' => 'Attendance', 'href' => '#'],
            'timeoff' => ['label' => 'Time Off', 'href' => '#'],
        ];
    } else {
        $items = [
            'profile' => ['label' => 'My Profile', 'href' => 'profile.php'],
            'attendance' => ['label' => 'Attendance', 'href' => '#'],
            'timeoff' => ['label' => 'Time Off', 'href' => '#'],
        ];
    }
    ?>
    <nav class="topbar__nav">
      <?php foreach ($items as $key => $item): ?>
        <?php
          $isDisabled = $item['href'] === '#';
          $classes = [];
          if ($key === $active) $classes[] = 'active';
          if ($isDisabled) $classes[] = 'disabled';
        ?>
        <a href="<?= htmlspecialchars($item['href']) ?>"
           <?= $classes ? 'class="' . htmlspecialchars(implode(' ', $classes)) . '"' : '' ?>
           <?= $isDisabled ? 'title="Coming soon" aria-disabled="true" tabindex="-1"' : '' ?>><?= htmlspecialchars($item['label']) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php
}
