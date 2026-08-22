<?php
require_once __DIR__ . '/store.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function current_hr(): ?array
{
    return $_SESSION['hr'] ?? null;
}

function current_employee_id(): ?string
{
    return $_SESSION['employee_id'] ?? null;
}

/** Redirects to $redirectTo (relative to the calling script) if the session role doesn't match. */
function require_role(string $role, string $redirectTo): void
{
    if (current_role() !== $role) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function attempt_hr_login(string $loginId, string $password): bool
{
    foreach (load_hr_accounts() as $acc) {
        if (hash_equals($acc['login_id'], $loginId) && password_verify($password, $acc['password_hash'])) {
            $_SESSION['role'] = 'hr';
            $_SESSION['hr'] = [
                'id' => $acc['id'],
                'name' => $acc['name'],
                'login_id' => $acc['login_id'],
                'company_name' => $acc['company_name'] ?? '—',
                'company_initials' => $acc['company_initials'] ?? 'CO',
            ];
            return true;
        }
    }
    return false;
}

/** Returns the employee id on success, or null on failure (bad credentials or account not yet activated). */
function attempt_employee_login(string $loginId, string $password): ?string
{
    foreach (load_employees() as $id => $emp) {
        if (($emp['login_id'] ?? null) !== $loginId) {
            continue;
        }
        if (empty($emp['password_hash'])) {
            return null;
        }
        if (password_verify($password, $emp['password_hash'])) {
            $_SESSION['role'] = 'employee';
            $_SESSION['employee_id'] = $id;
            return $id;
        }
        return null;
    }
    return null;
}

function logout_all(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
