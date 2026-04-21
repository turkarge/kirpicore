<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_once BASE_PATH . '/modules/settings/language.php';
require_once BASE_PATH . '/modules/notifications/language.php';
require_once BASE_PATH . '/modules/profile/language.php';
require_once BASE_PATH . '/modules/auth/language.php';

$user = current_user();
$currentRoutePath = $GLOBALS['current_route_path'] ?? '';
$unreadNotificationsCount = get_unread_notifications_count((int) ($user['id'] ?? 0));
$recentNotifications = get_recent_notifications((int) ($user['id'] ?? 0), 5);
$userAvatarUrl = !empty($user['avatar'])
    ? base_url('uploads/avatars/' . ltrim((string) $user['avatar'], '/'))
    : null;
$canUseLockFeature = $user
    && kirpi_auth_lock_schema_ready()
    && !empty($user['lock_enabled']);

$navToggleLabel = settings_lang('nav_toggle', 'Menuyu Ac/Kapat');
$navBellAria = notifications_lang('nav_bell_aria', notifications_lang('notifications', 'Notifications'));
$navNotificationsTitle = notifications_lang('notifications', 'Notifications');
$navNotificationsNew = notifications_lang('nav_new_badge', 'New');
$navNotificationsEmpty = notifications_lang('nav_empty', 'No notifications');
$navViewAllNotifications = notifications_lang('nav_view_all', 'View all notifications');
$navProfileLabel = profile_lang('profile', 'Profile');
$navUserMenuAria = profile_lang('nav_user_menu', 'User Menu');
$navUserFallback = profile_lang('user_fallback', 'User');
$navLockAction = auth_lang('nav_lock_session', 'Lock Session');
$navLogout = auth_lang('nav_logout', 'Logout');

$menu = function_exists('kirpi_navigation_menu_tree') ? kirpi_navigation_menu_tree() : [];

$filterVisibleMenuItems = static function (array $items) use (&$filterVisibleMenuItems): array {
    $visibleItems = [];

    foreach ($items as $item) {
        $hasChildren = isset($item['children']) && is_array($item['children']);

        if ($hasChildren) {
            $visibleChildren = $filterVisibleMenuItems($item['children']);
            if (empty($visibleChildren)) {
                continue;
            }

            $item['children'] = $visibleChildren;
            $visibleItems[] = $item;
            continue;
        }

        if (!route_exists($item['url'] ?? '')) {
            continue;
        }

        if (($item['permission'] ?? null) && !check_permission($item['permission'])) {
            continue;
        }

        $visibleItems[] = $item;
    }

    return $visibleItems;
};

$isMenuItemActive = static function (array $item, string $routePath) use (&$isMenuItemActive): bool {
    if (!empty($item['url']) && $item['url'] === $routePath) {
        return true;
    }

    if (isset($item['children']) && is_array($item['children'])) {
        foreach ($item['children'] as $childItem) {
            if ($isMenuItemActive($childItem, $routePath)) {
                return true;
            }
        }
    }

    return false;
};
?>

<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="<?php echo e($navToggleLabel); ?>">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="<?php echo base_url(APP_DEFAULT_ROUTE); ?>" class="text-decoration-none text-reset d-inline-flex align-items-center gap-2">
                <img src="<?php echo asset_url('img/logo.svg'); ?>" alt="<?php echo e(app_name()); ?>" class="kirpi-brand-logo">
                <?php echo e(app_name()); ?>
            </a>
        </div>

        <div class="navbar-nav flex-row order-md-last">
            <?php if ($user): ?>
                <?php if (route_exists('notifications/list') && check_permission('notifications.view')): ?>
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <a href="#"
                            class="nav-link px-0 position-relative js-notification-bell <?php echo $currentRoutePath === 'notifications/list' ? 'active' : ''; ?> <?php echo $unreadNotificationsCount > 0 ? 'kirpi-bell-has-unread' : ''; ?>"
                            data-unread-count="<?php echo (int) $unreadNotificationsCount; ?>"
                            data-bs-toggle="dropdown" tabindex="-1" aria-label="<?php echo e($navBellAria); ?>" aria-expanded="false">
                            <i class="ti ti-bell fs-2 kirpi-bell-icon"></i>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span class="badge bg-red badge-notification badge-pill position-absolute top-0 start-100 translate-middle js-notification-dot"></span>
                            <?php endif; ?>
                        </a>

                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="min-width: 24rem;">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title m-0"><?php echo e($navNotificationsTitle); ?></h3>
                                    <?php if ($unreadNotificationsCount > 0): ?>
                                        <span class="badge bg-red-lt"><?php echo e($navNotificationsNew); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="list-group list-group-flush list-group-hoverable">
                                    <?php if (empty($recentNotifications)): ?>
                                        <div class="list-group-item text-secondary">
                                            <?php echo e($navNotificationsEmpty); ?>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentNotifications as $notification): ?>
                                            <?php
                                            $isUnread = empty($notification['read_at']);
                                            $dateLabel = '';

                                            if (!empty($notification['created_at'])) {
                                                $timestamp = strtotime((string) $notification['created_at']);
                                                if ($timestamp !== false) {
                                                    $dateLabel = date('d.m.Y H:i', $timestamp);
                                                }
                                            }
                                            ?>
                                            <a href="<?php echo base_url('notifications/list'); ?>"
                                                class="list-group-item js-notification-item"
                                                data-notification-id="<?php echo (int) ($notification['id'] ?? 0); ?>"
                                                data-is-unread="<?php echo $isUnread ? '1' : '0'; ?>"
                                                data-mark-read-url="<?php echo base_url('notifications/actions/mark-read'); ?>">
                                                <div class="row align-items-start">
                                                    <div class="col-auto pt-1">
                                                        <span class="status-dot <?php echo $isUnread ? 'status-dot-animated bg-red' : 'bg-secondary'; ?> d-block js-notification-item-dot"></span>
                                                    </div>
                                                    <div class="col text-truncate">
                                                        <div class="text-body d-block"><?php echo e($notification['title'] ?? $navNotificationsTitle); ?></div>
                                                        <div class="d-block text-secondary text-truncate mt-1">
                                                            <?php echo e($notification['message'] ?? ''); ?>
                                                        </div>
                                                        <?php if ($dateLabel !== ''): ?>
                                                            <div class="mt-1 text-secondary small"><?php echo e($dateLabel); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer text-center">
                                    <a href="<?php echo base_url('notifications/list'); ?>" class="btn btn-sm btn-ghost-secondary w-100">
                                        <?php echo e($navViewAllNotifications); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nav-item d-md-none me-3">
                        <a href="<?php echo base_url('notifications/list'); ?>"
                            class="nav-link px-0 position-relative js-notification-bell <?php echo $currentRoutePath === 'notifications/list' ? 'active' : ''; ?> <?php echo $unreadNotificationsCount > 0 ? 'kirpi-bell-has-unread' : ''; ?>"
                            data-unread-count="<?php echo (int) $unreadNotificationsCount; ?>"
                            aria-label="<?php echo e($navNotificationsTitle); ?>">
                            <i class="ti ti-bell fs-2 kirpi-bell-icon"></i>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span class="badge bg-red badge-notification badge-pill position-absolute top-0 start-100 translate-middle js-notification-dot"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($canUseLockFeature && route_exists('auth/actions/lock')): ?>
                    <div class="nav-item d-none d-md-flex me-3">
                        <form action="<?php echo base_url('auth/actions/lock'); ?>" method="post" data-ajax="true" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button
                                type="submit"
                                class="nav-link px-0 border-0 bg-transparent"
                                title="<?php echo e($navLockAction); ?>"
                                aria-label="<?php echo e($navLockAction); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-key">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h5" />
                                    <path d="M18.5 18.5l-3.5 3.5l-1.5 -1.5" />
                                    <path d="M18.554 18.414a2 2 0 1 1 2.828 -2.828a2 2 0 0 1 -2.828 2.828" />
                                    <path d="M16 19l1 1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="nav-item dropdown">
                    <a href="#" id="user-menu-trigger" class="nav-link d-flex lh-1 text-reset p-0 dropdown-toggle"
                        data-bs-toggle="dropdown" aria-label="<?php echo e($navUserMenuAria); ?>" aria-expanded="false">
                        <?php if ($userAvatarUrl): ?>
                            <span class="avatar avatar-sm" style="background-image: url('<?php echo e($userAvatarUrl); ?>')"></span>
                        <?php else: ?>
                            <span class="avatar avatar-sm">
                                <?php echo e(mb_strtoupper(mb_substr($user['name'] ?? $navUserFallback, 0, 1))); ?>
                            </span>
                        <?php endif; ?>

                        <div class="d-none d-xl-block ps-2">
                            <div><?php echo e($user['name'] ?? $navUserFallback); ?></div>
                            <div class="mt-1 small text-secondary">
                                <?php echo e($user['role_name'] ?? ''); ?>
                            </div>
                        </div>
                    </a>

                    <div id="user-menu-dropdown" class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <?php if (route_exists('profile/view')): ?>
                            <a href="<?php echo base_url('profile/view'); ?>" class="dropdown-item"><?php echo e($navProfileLabel); ?></a>
                        <?php endif; ?>

                        <?php if ($canUseLockFeature && route_exists('auth/actions/lock')): ?>
                            <form action="<?php echo base_url('auth/actions/lock'); ?>" method="post" class="m-0" data-ajax="true">
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent">
                                    <?php echo e($navLockAction); ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <form action="<?php echo base_url('auth/actions/logout'); ?>" method="post" class="m-0"
                            data-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent">
                                <?php echo e($navLogout); ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                <ul class="navbar-nav">
                    <?php foreach ($menu as $item): ?>
                        <?php
                        $hasChildren = isset($item['children']) && is_array($item['children']);

                        if ($hasChildren) {
                            $visibleChildren = $filterVisibleMenuItems($item['children']);

                            if (empty($visibleChildren)) {
                                continue;
                            }
                        } else {
                            if (!route_exists($item['url'])) {
                                continue;
                            }

                            if ($item['permission'] && !check_permission($item['permission'])) {
                                continue;
                            }
                        }

                        $isActive = !$hasChildren && $currentRoutePath === ($item['url'] ?? '');
                        $isChildActive = $hasChildren && array_filter(
                            $visibleChildren,
                            static fn(array $child): bool => $isMenuItemActive($child, $currentRoutePath)
                        );
                        ?>

                        <?php if ($hasChildren): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php echo !empty($isChildActive) ? 'active' : ''; ?>"
                                    href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="<?php echo e($item['icon']); ?>"></i>
                                    </span>
                                    <span class="nav-link-title">
                                        <?php echo e($item['title']); ?>
                                    </span>
                                </a>

                                <div class="dropdown-menu">
                                    <?php foreach ($visibleChildren as $child): ?>
                                        <?php if (isset($child['children']) && is_array($child['children'])): ?>
                                            <?php $isNestedActive = $isMenuItemActive($child, $currentRoutePath); ?>
                                            <div class="dropend">
                                                <a class="dropdown-item dropdown-toggle <?php echo $isNestedActive ? 'active' : ''; ?>"
                                                    href="#"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-auto-close="outside"
                                                    aria-expanded="false">
                                                    <span class="d-flex align-items-center justify-content-between w-100">
                                                        <span><?php echo e($child['title']); ?></span>
                                                        <i class="ti ti-chevron-right opacity-75"></i>
                                                    </span>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <?php foreach ($child['children'] as $subChild): ?>
                                                        <a class="dropdown-item <?php echo $currentRoutePath === ($subChild['url'] ?? '') ? 'active' : ''; ?>"
                                                            href="<?php echo base_url($subChild['url']); ?>">
                                                            <?php echo e($subChild['title']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a class="dropdown-item <?php echo $currentRoutePath === ($child['url'] ?? '') ? 'active' : ''; ?>"
                                                href="<?php echo base_url($child['url']); ?>">
                                                <?php echo e($child['title']); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>"
                                    href="<?php echo base_url($item['url']); ?>">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="<?php echo e($item['icon']); ?>"></i>
                                    </span>
                                    <span class="nav-link-title">
                                        <?php echo e($item['title']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</header>
