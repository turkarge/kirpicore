<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$user = current_user();
$currentRoutePath = $GLOBALS['current_route_path'] ?? '';
$unreadNotificationsCount = get_unread_notifications_count((int) ($user['id'] ?? 0));
$recentNotifications = get_recent_notifications((int) ($user['id'] ?? 0), 5);
$userAvatarUrl = !empty($user['avatar'])
    ? base_url('uploads/avatars/' . ltrim((string) $user['avatar'], '/'))
    : null;

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
            [
                'title' => 'Mail Test',
                'icon' => 'ti ti-mail',
                'url' => 'mail/test',
                'permission' => 'mail.view',
            ],
            [
                'title' => 'Ayarlar',
                'icon' => 'ti ti-adjustments',
                'url' => 'settings/view',
                'permission' => 'settings.view',
            ],
            [
                'title' => 'API Test',
                'icon' => 'ti ti-api',
                'url' => 'settings/api-test',
                'permission' => 'settings.view',
            ],
            [
                'title' => 'Backup Restore',
                'icon' => 'ti ti-database-export',
                'url' => 'backup/view',
                'permission' => 'backup.view',
            ],
        ],
    ],
    [
        'title' => 'Monitoring',
        'icon' => 'ti ti-radar',
        'children' => [
            [
                'title' => 'Audit Log',
                'icon' => 'ti ti-list-details',
                'url' => 'audit/list',
                'permission' => 'audit.view',
            ],
            [
                'title' => 'Guvenlik Izleme',
                'icon' => 'ti ti-shield-check',
                'url' => 'security/view',
                'permission' => 'security.view',
            ],
            [
                'title' => 'Health Metrics',
                'icon' => 'ti ti-activity-heartbeat',
                'url' => 'health/view',
                'permission' => 'health.view',
            ],
            [
                'title' => 'Jobs Queue',
                'icon' => 'ti ti-clock-play',
                'url' => 'queue/view',
                'permission' => 'queue.view',
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
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <a href="#"
                            class="nav-link px-0 position-relative js-notification-bell <?php echo $currentRoutePath === 'notifications/list' ? 'active' : ''; ?> <?php echo $unreadNotificationsCount > 0 ? 'kirpi-bell-has-unread' : ''; ?>"
                            data-unread-count="<?php echo (int) $unreadNotificationsCount; ?>"
                            data-bs-toggle="dropdown" tabindex="-1" aria-label="Bildirimleri goster" aria-expanded="false">
                            <i class="ti ti-bell fs-2 kirpi-bell-icon"></i>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span class="badge bg-red badge-notification badge-pill position-absolute top-0 start-100 translate-middle js-notification-dot"></span>
                            <?php endif; ?>
                        </a>

                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="min-width: 24rem;">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title m-0">Bildirimler</h3>
                                    <?php if ($unreadNotificationsCount > 0): ?>
                                        <span class="badge bg-red-lt">Yeni</span>
                                    <?php endif; ?>
                                </div>

                                <div class="list-group list-group-flush list-group-hoverable">
                                    <?php if (empty($recentNotifications)): ?>
                                        <div class="list-group-item text-secondary">
                                            Henuz bildiriminiz bulunmuyor.
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
                                                        <div class="text-body d-block"><?php echo e($notification['title'] ?? 'Bildirim'); ?></div>
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
                                        Tum bildirimleri gor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nav-item d-md-none me-3">
                        <a href="<?php echo base_url('notifications/list'); ?>"
                            class="nav-link px-0 position-relative js-notification-bell <?php echo $currentRoutePath === 'notifications/list' ? 'active' : ''; ?> <?php echo $unreadNotificationsCount > 0 ? 'kirpi-bell-has-unread' : ''; ?>"
                            data-unread-count="<?php echo (int) $unreadNotificationsCount; ?>"
                            aria-label="Bildirimler">
                            <i class="ti ti-bell fs-2 kirpi-bell-icon"></i>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span class="badge bg-red badge-notification badge-pill position-absolute top-0 start-100 translate-middle js-notification-dot"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="nav-item dropdown">
                    <a href="#" id="user-menu-trigger" class="nav-link d-flex lh-1 text-reset p-0 dropdown-toggle"
                        data-bs-toggle="dropdown" aria-label="Kullanıcı Menüsü" aria-expanded="false">
                        <?php if ($userAvatarUrl): ?>
                            <span class="avatar avatar-sm" style="background-image: url('<?php echo e($userAvatarUrl); ?>')"></span>
                        <?php else: ?>
                            <span class="avatar avatar-sm">
                                <?php echo e(mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))); ?>
                            </span>
                        <?php endif; ?>

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
