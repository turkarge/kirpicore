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
        set_flash_message('info', 'Devam etmek için lütfen giriş yapın.');
        redirect(base_url('auth/login'));
    }
}
