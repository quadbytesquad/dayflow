<?php
/**
 * POST /api/save_salary.php?id=emp-0001
 * Persists salary changes made on an employee's Salary Info tab.
 * HR-only — session role is re-checked here even though the tab itself
 * is already hidden from non-HR views.
 */

require_once __DIR__ . '/../lib/auth.php';
header('Content-Type: application/json');

if (current_role() !== 'hr') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Only HR can edit salary information.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$id = $_GET['id'] ?? '';
$employees = load_employees();
$hr = current_hr();

if ($id === '' || !isset($employees[$id])) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Employee not found.']);
    exit;
}

if (($employees[$id]['hr_id'] ?? null) !== ($hr['id'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'You can only edit employees in your own company.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
    exit;
}

function num($v, float $default = 0): float
{
    return is_numeric($v) ? (float) $v : $default;
}

function pct($v, float $default = 0): float
{
    return max(0, min(100, num($v, $default)));
}

$salary = &$employees[$id]['salary'];

$wage = num($input['month_wage'] ?? null, $salary['month_wage']);
if ($wage < 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Wage cannot be negative.']);
    exit;
}

$salary['month_wage'] = $wage;
$salary['working_days_per_week'] = max(1, min(7, (int) num($input['working_days_per_week'] ?? 5, 5)));
$salary['break_time_hrs'] = max(0, num($input['break_time_hrs'] ?? 1, 1));

$c = &$salary['components'];
$c['basic']['percent'] = pct($input['components']['basic']['percent'] ?? null, $c['basic']['percent']);
$c['hra']['percent'] = pct($input['components']['hra']['percent'] ?? null, $c['hra']['percent']);
$c['standard_allowance']['amount'] = max(0, num($input['components']['standard_allowance']['amount'] ?? null, $c['standard_allowance']['amount']));
$c['performance_bonus']['percent'] = pct($input['components']['performance_bonus']['percent'] ?? null, $c['performance_bonus']['percent']);
$c['leave_travel_allowance']['percent'] = pct($input['components']['leave_travel_allowance']['percent'] ?? null, $c['leave_travel_allowance']['percent']);
unset($c);

$salary['pf']['employee_percent'] = pct($input['pf']['employee_percent'] ?? null, $salary['pf']['employee_percent']);
$salary['pf']['employer_percent'] = pct($input['pf']['employer_percent'] ?? null, $salary['pf']['employer_percent']);
$salary['professional_tax'] = max(0, num($input['professional_tax'] ?? null, $salary['professional_tax']));

$calc = calculate_salary($salary);
unset($salary);

if (!save_employees($employees)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not write to storage.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'over_cap' => $calc['over_cap'],
    'fixed_allowance' => $calc['fixed_allowance'],
]);
