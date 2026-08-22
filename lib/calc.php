<?php
/**
 * Pure calculation + formatting helpers. No session, no I/O — safe to
 * include anywhere.
 */

/**
 * - Basic Salary            = % of Wage
 * - House Rent Allowance    = % of Basic
 * - Standard Allowance      = fixed amount
 * - Performance Bonus       = % of Wage
 * - Leave Travel Allowance  = % of Basic
 * - Fixed Allowance         = Wage − sum(all other components)
 */
function calculate_salary(array $salary): array
{
    $wage = (float) ($salary['month_wage'] ?? 0);
    $c    = $salary['components'] ?? [];

    $basicPct = (float) ($c['basic']['percent'] ?? 0);
    $hraPct = (float) ($c['hra']['percent'] ?? 0);
    $standard = (float) ($c['standard_allowance']['amount'] ?? 0);
    $bonusPct = (float) ($c['performance_bonus']['percent'] ?? 0);
    $ltaPct = (float) ($c['leave_travel_allowance']['percent'] ?? 0);

    $basic   = round($wage * ($basicPct / 100), 2);
    $hra     = round($basic * ($hraPct / 100), 2);
    $bonus   = round($wage * ($bonusPct / 100), 2);
    $lta     = round($basic * ($ltaPct / 100), 2);

    $subtotal = $basic + $hra + $standard + $bonus + $lta;
    $fixed    = round($wage - $subtotal, 2);
    $total    = $basic + $hra + $standard + $bonus + $lta + $fixed;

    $pfEmployeePct = (float) ($salary['pf']['employee_percent'] ?? 0);
    $pfEmployerPct = (float) ($salary['pf']['employer_percent'] ?? 0);

    return [
        'basic' => $basic,
        'hra' => $hra,
        'standard_allowance' => $standard,
        'performance_bonus' => $bonus,
        'leave_travel_allowance' => $lta,
        'fixed_allowance' => $fixed,
        'total' => $total,
        'over_cap' => $fixed < 0,
        'pf_employee' => round($basic * ($pfEmployeePct / 100), 2),
        'pf_employer' => round($basic * ($pfEmployerPct / 100), 2),
        'professional_tax' => (float) ($salary['professional_tax'] ?? 0),
    ];
}

function inr(float $n): string
{
    return number_format($n, 2);
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = array_map(fn($p) => strtoupper(substr($p, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $letters) ?: '?';
}

/** Default salary structure for a newly created employee — HR fills in the real wage afterwards. */
function default_salary_template(): array
{
    return [
        'wage_type' => 'Fixed Wage',
        'month_wage' => 0,
        'working_days_per_week' => 5,
        'break_time_hrs' => 1,
        'components' => [
            'basic' => ['label' => 'Basic Salary', 'basis' => 'wage', 'percent' => 50, 'note' => 'Defined as % of monthly wage'],
            'hra' => ['label' => 'House Rent Allowance', 'basis' => 'basic', 'percent' => 50, 'note' => '% of basic salary'],
            'standard_allowance' => ['label' => 'Standard Allowance', 'basis' => 'fixed', 'amount' => 0, 'note' => 'Fixed amount, pro-rated on wage change'],
            'performance_bonus' => ['label' => 'Performance Bonus', 'basis' => 'wage', 'percent' => 8.33, 'note' => 'Variable, paid during payroll'],
            'leave_travel_allowance' => ['label' => 'Leave Travel Allowance', 'basis' => 'basic', 'percent' => 8.33, 'note' => '% of basic salary'],
            'fixed_allowance' => ['label' => 'Fixed Allowance', 'basis' => 'remainder', 'note' => 'Wage minus total of all other components'],
        ],
        'pf' => ['employee_percent' => 12, 'employer_percent' => 12],
        'professional_tax' => 200,
    ];
}
