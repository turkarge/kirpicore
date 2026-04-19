<?php

function kirpi_core_permission_catalog(): array
{
    return [
        'dashboard' => [
            'title' => 'Dashboard',
            'permissions' => [
                [
                    'name' => 'Dashboard Görüntüleme',
                    'slug' => 'dashboard.view',
                ],
            ],
        ],
        'users' => [
            'title' => 'Kullanıcılar',
            'permissions' => [
                [
                    'name' => 'Kullanıcıları Görüntüleme',
                    'slug' => 'users.view',
                ],
                [
                    'name' => 'Kullanıcı Oluşturma',
                    'slug' => 'users.create',
                ],
                [
                    'name' => 'Kullanıcı Düzenleme',
                    'slug' => 'users.edit',
                ],
                [
                    'name' => 'Kullanıcı Durumu Güncelleme',
                    'slug' => 'users.status',
                ],
            ],
        ],
        'roles' => [
            'title' => 'Roller',
            'permissions' => [
                [
                    'name' => 'Rolleri Görüntüleme',
                    'slug' => 'roles.view',
                ],
                [
                    'name' => 'Rol Oluşturma',
                    'slug' => 'roles.create',
                ],
                [
                    'name' => 'Rol Düzenleme',
                    'slug' => 'roles.edit',
                ],
                [
                    'name' => 'Rol Durumu Güncelleme',
                    'slug' => 'roles.status',
                ],
                [
                    'name' => 'Rol Yetkilerini Yönetme',
                    'slug' => 'roles.permissions',
                ],
            ],
        ],
        'profile' => [
            'title' => 'Profil',
            'permissions' => [
                [
                    'name' => 'Profili Görüntüleme',
                    'slug' => 'profile.view',
                ],
                [
                    'name' => 'Profili Güncelleme',
                    'slug' => 'profile.edit',
                ],
            ],
        ],
        'notifications' => [
            'title' => 'Bildirimler',
            'permissions' => [
                [
                    'name' => 'Bildirimleri Görüntüleme',
                    'slug' => 'notifications.view',
                ],
                [
                    'name' => 'Bildirim Ayarlarını Yönetme',
                    'slug' => 'notifications.settings',
                ],
            ],
        ],
        'mail' => [
            'title' => 'Mail',
            'permissions' => [
                [
                    'name' => 'Mail Modulu Goruntuleme',
                    'slug' => 'mail.view',
                ],
                [
                    'name' => 'Test Maili Gonderme',
                    'slug' => 'mail.test',
                ],
            ],
        ],
        'audit' => [
            'title' => 'Audit',
            'permissions' => [
                [
                    'name' => 'Audit Log Goruntuleme',
                    'slug' => 'audit.view',
                ],
            ],
        ],
        'settings' => [
            'title' => 'Ayarlar',
            'permissions' => [
                [
                    'name' => 'Ayarlari Goruntuleme',
                    'slug' => 'settings.view',
                ],
                [
                    'name' => 'Ayarlari Guncelleme',
                    'slug' => 'settings.update',
                ],
            ],
        ],
        'queue' => [
            'title' => 'Queue',
            'permissions' => [
                [
                    'name' => 'Queue Goruntuleme',
                    'slug' => 'queue.view',
                ],
                [
                    'name' => 'Queue Yonetimi',
                    'slug' => 'queue.manage',
                ],
            ],
        ],
        'backup' => [
            'title' => 'Backup',
            'permissions' => [
                [
                    'name' => 'Backup Goruntuleme',
                    'slug' => 'backup.view',
                ],
                [
                    'name' => 'Backup Olusturma',
                    'slug' => 'backup.create',
                ],
                [
                    'name' => 'Backup Restore',
                    'slug' => 'backup.restore',
                ],
            ],
        ],
        'security' => [
            'title' => 'Guvenlik',
            'permissions' => [
                [
                    'name' => 'Guvenlik Izleme Goruntuleme',
                    'slug' => 'security.view',
                ],
            ],
        ],
    ];
}

function kirpi_flatten_permission_catalog(): array
{
    $permissions = [];

    foreach (kirpi_core_permission_catalog() as $groupKey => $group) {
        foreach ($group['permissions'] ?? [] as $permission) {
            $permissions[] = [
                'group_name' => $groupKey,
                'group_title' => $group['title'] ?? $groupKey,
                'name' => $permission['name'],
                'slug' => $permission['slug'],
            ];
        }
    }

    return $permissions;
}

function get_role_permission_slugs(int $roleId): array
{
    if ($roleId <= 0 || !db_table_exists('permissions') || !db_table_exists('role_permissions')) {
        return [];
    }

    try {
        $stmt = db()->prepare("
            SELECT p.slug
            FROM role_permissions rp
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.slug ASC
        ");
        $stmt->execute([
            ':role_id' => $roleId,
        ]);

        return array_values(array_unique($stmt->fetchAll(PDO::FETCH_COLUMN)));
    } catch (Throwable $e) {
        error_log('Role permission fetch error: ' . $e->getMessage());
        return [];
    }
}

function sync_permission_catalog(): void
{
    if (!db_table_exists('permissions')) {
        return;
    }

    $catalog = kirpi_flatten_permission_catalog();

    foreach ($catalog as $permission) {
        try {
            $stmt = db()->prepare("
                INSERT INTO permissions (name, slug, group_name)
                VALUES (:name, :slug, :group_name)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    group_name = VALUES(group_name)
            ");
            $stmt->execute([
                ':name' => $permission['name'],
                ':slug' => $permission['slug'],
                ':group_name' => $permission['group_name'],
            ]);
        } catch (Throwable $e) {
            error_log('Permission catalog sync error: ' . $e->getMessage());
            return;
        }
    }
}

function sync_role_permissions(int $roleId, array $permissionSlugs): void
{
    if ($roleId <= 0 || !db_table_exists('permissions') || !db_table_exists('role_permissions')) {
        return;
    }

    sync_permission_catalog();

    $allowedSlugs = array_column(kirpi_flatten_permission_catalog(), 'slug');
    $filteredSlugs = array_values(array_unique(array_intersect($allowedSlugs, $permissionSlugs)));

    $deleteStmt = db()->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
    $deleteStmt->execute([
        ':role_id' => $roleId,
    ]);

    if (empty($filteredSlugs)) {
        return;
    }

    $permissionStmt = db()->prepare('SELECT id, slug FROM permissions WHERE slug = :slug LIMIT 1');
    $insertStmt = db()->prepare("
        INSERT INTO role_permissions (role_id, permission_id)
        VALUES (:role_id, :permission_id)
    ");

    foreach ($filteredSlugs as $slug) {
        $permissionStmt->execute([
            ':slug' => $slug,
        ]);

        $permission = $permissionStmt->fetch(PDO::FETCH_ASSOC);
        if (!$permission) {
            continue;
        }

        $insertStmt->execute([
            ':role_id' => $roleId,
            ':permission_id' => (int) $permission['id'],
        ]);
    }
}
