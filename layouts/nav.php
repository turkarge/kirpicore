<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$user = current_user();
$currentRoutePath = $GLOBALS['current_route_path'] ?? '';
$unreadNotificationsCount = get_unread_notifications_count((int) ($user['id'] ?? 0));

$menu = [
    [
        'title' => 'Dashboard',
        'icon' => 'ti ti-home',
        'url' => 'dashboard/view',
        'permission' => null,
    ],
    [
        'title' => 'Yönetim',
        'icon' => 'ti ti-settings',
        'children' => [
            [
                'title' => 'Kullanıcılar',
                'icon' => 'ti ti-users',
                'url' => 'users/view',
                'permission' => 'users.view',
            ],
            [
                'title' => 'Roller',
                'icon' => 'ti ti-shield',
                'url' => 'roles/view',
                'permission' => 'roles.view',
            ],
            [
                'title' => 'Bildirimler',
                'icon' => 'ti ti-bell',
                'url' => 'notifications/list',
                'permission' => 'notifications.view',
            ],
        ],
    ],
];

$filterVisibleMenuItems = static function (array $items): array {
    $visibleItems = [];

    foreach ($items as $item) {
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
?>

<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Menüyü Aç/Kapat">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="<?php echo base_url(APP_DEFAULT_ROUTE); ?>" class="text-decoration-none text-reset">
                <?php echo e(app_name()); ?>
            </a>
        </div>

        <div class="navbar-nav flex-row order-md-last">
            <?php if ($user): ?>
                <?php if (route_exists('notifications/list') && check_permission('notifications.view')): ?>
                    <div class="nav-item me-3">
                        <a href="<?php echo base_url('notifications/list'); ?>"
                            class="nav-link px-0 position-relative <?php echo $currentRoutePath === 'notifications/list' ? 'active' : ''; ?>"
                            aria-label="Bildirimler">
                            <i class="ti ti-bell fs-2"></i>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span
                                    class="badge badge-sm bg-red text-white badge-notification badge-pill position-absolute top-0 start-100 translate-middle">
                                    <?php echo $unreadNotificationsCount > 99 ? '99+' : (int) $unreadNotificationsCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="nav-item dropdown">
                    <a href="#" id="user-menu-trigger" class="nav-link d-flex lh-1 text-reset p-0 dropdown-toggle"
                        data-bs-toggle="dropdown" aria-label="Kullanıcı Menüsü" aria-expanded="false">
                        <span class="avatar avatar-sm">
                            <?php echo e(mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))); ?>
                        </span>

                        <div class="d-none d-xl-block ps-2">
                            <div><?php echo e($user['name'] ?? 'User'); ?></div>
                            <div class="mt-1 small text-secondary">
                                <?php echo e($user['role_name'] ?? ''); ?>
                            </div>
                        </div>
                    </a>

                    <div id="user-menu-dropdown" class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <?php if (route_exists('profile/view')): ?>
                            <a href="<?php echo base_url('profile/view'); ?>" class="dropdown-item">Profil</a>
                        <?php endif; ?>

                        <form action="<?php echo base_url('auth/actions/logout'); ?>" method="post" class="m-0"
                            data-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent">
                                Çıkış
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
                            static fn(array $child): bool => ($child['url'] ?? '') === $currentRoutePath
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
                                        <a class="dropdown-item <?php echo $currentRoutePath === ($child['url'] ?? '') ? 'active' : ''; ?>"
                                            href="<?php echo base_url($child['url']); ?>">
                                            <?php echo e($child['title']); ?>
                                        </a>
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