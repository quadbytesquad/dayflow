<?php
require_once __DIR__ . '/calc.php';

define('EMPLOYEES_PATH', __DIR__ . '/../data/employees.json');
define('HR_ACCOUNTS_PATH', __DIR__ . '/../data/hr_accounts.json');

function ensure_data_dir(): void
{
    $dir = dirname(EMPLOYEES_PATH);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

/** Seed employee so the app has something to show on first run. */
function default_employees(): array
{
    return [
        'emp-2045' => [
            'name' => 'Ananya Sharma',
            'department' => 'Product Design',
            'manager' => 'Rahul Verma',
            'location' => 'Pune, IN',
            'email' => 'ananya.sharma@company.com',
            'mobile' => '+91 98765 43210',
            'login_id' => 'ananya.sharma',
            'password_hash' => password_hash('Welcome123!', PASSWORD_DEFAULT),
            'date_of_joining' => date('Y-m-d'),
            'status' => 'active',
            'salary' => [
                'wage_type' => 'Fixed Wage',
                'month_wage' => 50000,
                'working_days_per_week' => 5,
                'break_time_hrs' => 1,
                'components' => [
                    'basic' => ['label' => 'Basic Salary', 'basis' => 'wage', 'percent' => 50, 'note' => 'Defined as % of monthly wage'],
                    'hra' => ['label' => 'House Rent Allowance', 'basis' => 'basic', 'percent' => 50, 'note' => '% of basic salary'],
                    'standard_allowance' => ['label' => 'Standard Allowance', 'basis' => 'fixed', 'amount' => 4167, 'note' => 'Fixed amount, pro-rated on wage change'],
                    'performance_bonus' => ['label' => 'Performance Bonus', 'basis' => 'wage', 'percent' => 8.33, 'note' => 'Variable, paid during payroll'],
                    'leave_travel_allowance' => ['label' => 'Leave Travel Allowance', 'basis' => 'basic', 'percent' => 8.33, 'note' => '% of basic salary'],
                    'fixed_allowance' => ['label' => 'Fixed Allowance', 'basis' => 'remainder', 'note' => 'Wage minus total of all other components'],
                ],
                'pf' => ['employee_percent' => 12, 'employer_percent' => 12],
                'professional_tax' => 200,
            ],
        ],
    ];
}

/** Seed HR account — demo credentials only, shown on the HR login page. */
function default_hr_accounts(): array
{
    return [
        [
            'id' => 'hr-001',
            'company_name' => 'Odoo India',
            'company_initials' => 'OI',
            'name' => 'Priya Nair',
            'email' => 'priya.nair@odooindia.com',
            'phone' => '—',
            'login_id' => 'priya.nair',
            'password_hash' => password_hash('HrDemo123!', PASSWORD_DEFAULT),
            'logo_path' => null,
        ],
    ];
}

function load_employees(): array
{
    ensure_data_dir();
    if (!file_exists(EMPLOYEES_PATH)) {
        $seed = default_employees();
        save_employees($seed);
        return $seed;
    }
    $raw = @file_get_contents(EMPLOYEES_PATH);
    $decoded = $raw !== false ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        $seed = default_employees();
        save_employees($seed);
        return $seed;
    }
    return $decoded;
}

function save_employees(array $employees): bool
{
    ensure_data_dir();
    return file_put_contents(EMPLOYEES_PATH, json_encode($employees, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function load_hr_accounts(): array
{
    ensure_data_dir();
    if (!file_exists(HR_ACCOUNTS_PATH)) {
        $seed = default_hr_accounts();
        save_hr_accounts($seed);
        return $seed;
    }
    $raw = @file_get_contents(HR_ACCOUNTS_PATH);
    $decoded = $raw !== false ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        $seed = default_hr_accounts();
        save_hr_accounts($seed);
        return $seed;
    }
    return $decoded;
}

function save_hr_accounts(array $accounts): bool
{
    ensure_data_dir();
    return file_put_contents(HR_ACCOUNTS_PATH, json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

/** Saves an uploaded company logo to assets/uploads/logos and returns its web-relative path, or null on failure. */
function save_uploaded_logo(array $file, string $companyName): ?string
{
    if (!isset($file['size']) || $file['size'] > 2 * 1024 * 1024) {
        return null;
    }
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
    $mime = function_exists('mime_content_type') ? @mime_content_type($file['tmp_name']) : null;
    if ($mime === null || !isset($allowed[$mime])) {
        return null;
    }

    $dir = __DIR__ . '/../assets/uploads/logos';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return null;
    }

    $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/', '-', $companyName), '-'));
    if ($slug === '') {
        $slug = 'company';
    }
    $filename = $slug . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return null;
    }
    return 'assets/uploads/logos/' . $filename;
}

/** Short uppercase initials used as the company prefix in generated employee login IDs, e.g. "Odoo India" -> "OI". */
function company_initials(string $companyName): string
{
    $words = array_filter(preg_split('/\s+/', trim($companyName)));
    $letters = array_map(fn($w) => strtoupper(substr($w, 0, 1)), $words);
    $initials = implode('', $letters);
    return $initials !== '' ? substr($initials, 0, 4) : 'CO';
}

function generate_employee_id(array $employees): string
{
    $n = 1;
    do {
        $id = 'emp-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
        $n++;
    } while (isset($employees[$id]));
    return $id;
}

/**
 * Generates a login ID in the format:
 * [Company Initials] [first 2 letters of first name + first 2 letters of last name] [year of joining] [4-digit serial for that year]
 * e.g. "OI" + "JODO" + "2022" + "0001" -> "OIJODO20220001"  (for "John Doe", joining Odoo India in 2022, 1st hire that year)
 */
function generate_login_id(string $employeeName, string $companyInitials, string $joinYear, array $employees): string
{
    $parts = array_values(array_filter(preg_split('/\s+/', trim($employeeName)) ?: []));
    $first = $parts[0] ?? '';
    $last = $parts[count($parts) - 1] ?? $first;

    $namePart = strtoupper(substr($first, 0, 2) . substr($last, 0, 2));
    $namePart = str_pad($namePart, 4, 'X'); // pad short names (e.g. single-letter first/last name)

    $serial = next_join_serial($employees, $joinYear);

    return strtoupper($companyInitials) . $namePart . $joinYear . $serial;
}

/** Returns the next 4-digit serial number for employees joining in the given year (e.g. "0001", "0002", ...). */
function next_join_serial(array $employees, string $joinYear): string
{
    $count = 0;
    foreach ($employees as $emp) {
        if (isset($emp['date_of_joining']) && substr((string) $emp['date_of_joining'], 0, 4) === $joinYear) {
            $count++;
        }
    }
    return str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
}

function generate_temp_password(int $length = 10): string
{
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#%';
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}
