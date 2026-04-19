<?php

function is_user_logged_in(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function check_permission(?string $permissionKey): bool
{
    if ($permissionKey === null || $permissionKey === '') {
        return true;
    }

    if (!is_user_logged_in()) {
        return false;
    }

    $user = current_user();

    if (($user['role_name'] ?? null) === 'Super Admin') {
        return true;
    }

    return in_array($permissionKey, $user['permissions'] ?? [], true);
}

function require_login(): void
{
    if (!is_user_logged_in()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? BASE_URL;
        set_flash_message('info', 'Devam etmek icin lutfen giris yapin.');
        redirect(base_url('auth/login'));
    }
}

function validate_active_session_user(): bool
{
    if (!is_user_logged_in()) {
        return false;
    }

    if (!db_table_exists('users')) {
        return true;
    }

    $userId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($userId <= 0) {
        return false;
    }

    try {
        $stmt = db()->prepare("
            SELECT
                u.id,
                u.name,
                u.email,
                u.avatar,
                u.is_active,
                u.role_id,
                r.name AS role_name,
                r.is_active AS role_is_active
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $userId,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return false;
        }

        if ((int) ($user['is_active'] ?? 0) !== 1) {
            return false;
        }

        if (($user['role_id'] ?? null) && isset($user['role_is_active']) && (int) $user['role_is_active'] !== 1) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'avatar' => $user['avatar'] ?? null,
            'role_id' => isset($user['role_id']) ? (int) $user['role_id'] : null,
            'role_name' => $user['role_name'] ?? null,
            'permissions' => load_user_permissions(
                isset($user['role_id']) ? (int) $user['role_id'] : null,
                $user['role_name'] ?? null
            ),
        ];

        return true;
    } catch (Throwable $e) {
        error_log('Session user validation error: ' . $e->getMessage());
        return false;
    }
}
