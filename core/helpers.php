<?php

function env(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }

    return $default;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env($key);

    if ($value === null) {
        return $default;
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
}

function db(): PDO
{
    global $pdo;
    return $pdo;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function route_exists(string $path): bool
{
    global $routes;

    return isset($routes[$path]);
}

function db_table_exists(string $tableName): bool
{
    static $cache = [];

    if (isset($cache[$tableName])) {
        return $cache[$tableName];
    }

    try {
        $stmt = db()->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = :table_name
        ");
        $stmt->execute([
            ':table_name' => $tableName,
        ]);

        $cache[$tableName] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$tableName] = false;
    }

    return $cache[$tableName];
}

function db_column_exists(string $tableName, string $columnName): bool
{
    static $cache = [];
    $cacheKey = $tableName . '.' . $columnName;

    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    try {
        $stmt = db()->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = :table_name
              AND column_name = :column_name
        ");
        $stmt->execute([
            ':table_name' => $tableName,
            ':column_name' => $columnName,
        ]);

        $cache[$cacheKey] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$cacheKey] = false;
    }

    return $cache[$cacheKey];
}

function get_roles_for_select(?int $selectedRoleId = null, bool $activeOnly = false): array
{
    if (!db_table_exists('roles')) {
        return [];
    }

    try {
        $hasIsActiveColumn = db_column_exists('roles', 'is_active');

        if ($hasIsActiveColumn && $activeOnly) {
            $sql = "
                SELECT id, name, is_active
                FROM roles
                WHERE is_active = 1
            ";

            $params = [];

            if ($selectedRoleId && $selectedRoleId > 0) {
                $sql .= " OR id = :selected_role_id";
                $params[':selected_role_id'] = $selectedRoleId;
            }

            $sql .= " ORDER BY is_active DESC, name ASC";

            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($hasIsActiveColumn) {
            $stmt = db()->query('SELECT id, name, is_active FROM roles ORDER BY is_active DESC, name ASC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = db()->query('SELECT id, name, 1 AS is_active FROM roles ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('Role select load error: ' . $e->getMessage());
        return [];
    }
}

function load_user_permissions(?int $roleId, ?string $roleName = null): array
{
    static $cache = [];

    if (($roleName ?? null) === 'Super Admin') {
        return ['*'];
    }

    if (!$roleId) {
        return [];
    }

    if (isset($cache[$roleId])) {
        return $cache[$roleId];
    }

    if (!db_table_exists('permissions') || !db_table_exists('role_permissions')) {
        $cache[$roleId] = [];
        return $cache[$roleId];
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

        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cache[$roleId] = array_values(array_unique(array_filter($permissions)));
    } catch (Throwable $e) {
        error_log('Permission load error: ' . $e->getMessage());
        $cache[$roleId] = [];
    }

    return $cache[$roleId];
}

function get_unread_notifications_count(?int $userId): int
{
    if (!$userId || !db_table_exists('notifications')) {
        return 0;
    }

    try {
        $stmt = db()->prepare("
            SELECT COUNT(id)
            FROM notifications
            WHERE user_id = :user_id
              AND read_at IS NULL
        ");
        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('Unread notifications count error: ' . $e->getMessage());
        return 0;
    }
}

function get_recent_notifications(?int $userId, int $limit = 5): array
{
    if (!$userId || !db_table_exists('notifications')) {
        return [];
    }

    $limit = max(1, min(20, $limit));

    try {
        $stmt = db()->prepare("
            SELECT
                id,
                title,
                message,
                channel,
                created_at,
                read_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('Recent notifications fetch error: ' . $e->getMessage());
        return [];
    }
}

function app_name(): string
{
    return APP_NAME;
}

function app_ver(): string
{
    return APP_VER;
}

function base_url(string $path = ''): string
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base : $base . '/' . $path;
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash_message(): ?array
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

function kirpi_upload_avatar(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Görsel yüklenemedi.',
        ];
    }

    $maxSize = 2 * 1024 * 1024;
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (($file['size'] ?? 0) > $maxSize) {
        return [
            'success' => false,
            'message' => 'Görsel boyutu 2 MB sınırını aşıyor.',
        ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimeTypes[$mimeType])) {
        return [
            'success' => false,
            'message' => 'Desteklenmeyen görsel formatı.',
        ];
    }

    $extension = $allowedMimeTypes[$mimeType];
    $fileName = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $extension;

    $uploadDir = BASE_PATH . '/uploads/avatars';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return [
            'success' => false,
            'message' => 'Yükleme dizini oluşturulamadı.',
        ];
    }

    $targetPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => false,
            'message' => 'Görsel sunucuya kaydedilemedi.',
        ];
    }

    return [
        'success' => true,
        'file_name' => $fileName,
    ];
}

function get_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', (string) $token);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_pagination(int $current_page, int $total_pages, int $links_to_show = 2): void
{
    if ($total_pages <= 1) {
        return;
    }

    echo '<ul class="pagination m-0 ms-auto">';

    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
    echo "<li class='page-item {$prev_disabled}'>
            <a class='page-link' href='#' data-page='" . ($current_page - 1) . "'>Önceki</a>
          </li>";

    for ($i = 1; $i <= $total_pages; $i++) {
        if (
            $i === 1 ||
            $i === $total_pages ||
            ($i >= $current_page - $links_to_show && $i <= $current_page + $links_to_show)
        ) {
            $active_class = ($i === $current_page) ? 'active' : '';
            echo "<li class='page-item {$active_class}'>
                    <a class='page-link' href='#' data-page='{$i}'>{$i}</a>
                  </li>";
        } elseif (
            $i === $current_page - ($links_to_show + 1) ||
            $i === $current_page + ($links_to_show + 1)
        ) {
            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }

    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
    echo "<li class='page-item {$next_disabled}'>
            <a class='page-link' href='#' data-page='" . ($current_page + 1) . "'>Sonraki</a>
          </li>";

    echo '</ul>';
}

function resolve_page_script(?string $routeFile): ?string
{
    if (!$routeFile) {
        return null;
    }

    $scriptPath = str_replace(['pages/', '.php'], ['scripts/', '.js'], $routeFile);
    $fullPath = BASE_PATH . '/' . $scriptPath;

    return is_file($fullPath) ? $scriptPath : null;
}
