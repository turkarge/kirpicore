<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function kirpi_templates_table_ready(): bool
{
    return db_table_exists('templates');
}

function kirpi_template_kinds(): array
{
    return ['email', 'print', 'content'];
}

function kirpi_template_supported_modules(): array
{
    return [
        'auth' => 'Auth',
        'mail' => 'Mail',
        'users' => 'Users',
        'notifications' => 'Notifications',
        'queue' => 'Queue',
        'backup' => 'Backup',
        'audit' => 'Audit',
        'ai' => 'Kirpi Intelligence',
        'core' => 'Core',
    ];
}

function kirpi_template_supported_targets(string $kind): array
{
    $targets = [
        'email' => [
            'auth.password_reset' => 'Şifre sıfırlama',
            'mail.test_manual' => 'Manuel test maili',
            'queue.test_mail' => 'Queue test maili',
            'users.session_dropped' => 'Kullanıcı oturum düşürme',
            'users.lock_key_reset' => 'Kullanıcı lock key sıfırlama',
            'notifications.generic' => 'Genel bildirim',
            'audit.summary' => 'Audit özeti',
            'ai.summary' => 'AI özeti',
        ],
        'print' => [
            'audit.overview' => 'Audit genel görünüm',
            'users.list' => 'Kullanıcı listesi',
            'roles.list' => 'Rol listesi',
            'system.report' => 'Sistem raporu',
        ],
        'content' => [
            'notifications.generic' => 'Genel bildirim',
            'users.session_dropped' => 'Kullanıcı oturum düşürme bildirimi',
            'users.lock_key_reset' => 'Kullanıcı lock key sıfırlama bildirimi',
            'backup.completed' => 'Backup tamamlandı bildirimi',
            'ai.schema_synced' => 'AI schema sync bildirimi',
            'queue.job_failed' => 'Queue job hata bildirimi',
            'dashboard.notice' => 'Dashboard duyurusu',
            'system.footer' => 'Sistem footer içeriği',
            'terms.content' => 'Kullanım koşulları',
        ],
    ];

    return $targets[$kind] ?? [];
}

function kirpi_template_normalize_code(string $value): string
{
    return strtolower(trim($value));
}

function kirpi_template_normalize_variables(string|array|null $variables): array
{
    if (is_array($variables)) {
        $items = $variables;
    } else {
        $items = preg_split('/[\s,]+/', trim((string) $variables)) ?: [];
    }

    $normalized = [];
    foreach ($items as $item) {
        $item = trim((string) $item);
        $item = trim($item, '{} ');
        if ($item === '') {
            continue;
        }
        $normalized[] = $item;
    }

    $normalized = array_values(array_unique($normalized));
    sort($normalized);

    return $normalized;
}

function kirpi_template_variables_for_target(string $targetKey): array
{
    $variables = [
        'app_name',
        'app_url',
        'year',
    ];

    $targetVariables = [
        'auth.password_reset' => ['user_name', 'reset_link', 'expires_minutes'],
        'mail.test_manual' => ['message_html'],
        'queue.test_mail' => ['user_name', 'sent_at'],
        'users.session_dropped' => ['user_name'],
        'users.lock_key_reset' => ['user_name'],
        'notifications.generic' => ['title', 'message', 'action_url'],
        'backup.completed' => ['label', 'file_name', 'file_size'],
        'ai.schema_synced' => ['entity_count', 'field_count'],
        'queue.job_failed' => ['job_type', 'error_message'],
        'audit.summary' => ['period', 'total_events', 'failed_events'],
        'ai.summary' => ['entity_count', 'field_count', 'audit_count'],
        'audit.overview' => ['generated_at', 'total_events'],
        'users.list' => ['generated_at', 'user_count'],
        'roles.list' => ['generated_at', 'role_count'],
        'system.report' => ['generated_at', 'app_version'],
    ];

    return kirpi_template_normalize_variables(array_merge($variables, $targetVariables[$targetKey] ?? []));
}

function kirpi_template_render_string(string $body, array $context, bool $escape = true): string
{
    $flat = [];
    $walker = function (array $data, string $prefix = '') use (&$walker, &$flat, $escape): void {
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;
            if (is_array($value)) {
                $walker($value, $path);
                continue;
            }

            $stringValue = (string) ($value ?? '');
            $flat['{{' . $path . '}}'] = $escape ? htmlspecialchars($stringValue, ENT_QUOTES, 'UTF-8') : $stringValue;
            $flat['{{{' . $path . '}}}'] = $stringValue;
        }
    };
    $walker($context);

    return strtr($body, $flat);
}

function kirpi_template_extract_placeholders(string $content): array
{
    if ($content === '') {
        return [];
    }

    if (preg_match_all('/\{\{\{?\s*([a-zA-Z0-9_.-]+)\s*\}?\}\}/', $content, $matches) !== 1) {
        return [];
    }

    return kirpi_template_normalize_variables($matches[1] ?? []);
}

function kirpi_template_kind_module_for_key(string $templateKey): string
{
    $parts = explode('.', $templateKey, 2);
    $moduleKey = trim((string) ($parts[0] ?? ''));

    return array_key_exists($moduleKey, kirpi_template_supported_modules()) ? $moduleKey : 'mail';
}

function kirpi_template_default_notification_templates(): array
{
    return [
        'notifications.generic' => [
            'name' => 'Notifications - Generic',
            'subject' => '{{title}}',
            'body' => '{{message}}',
        ],
        'users.session_dropped' => [
            'name' => 'Users - Session Dropped Notification',
            'subject' => 'Oturum sonlandırıldı',
            'body' => 'Yetkili bir kullanıcı tüm aktif oturumlarınızı sonlandırdı. Lütfen yeniden giriş yapın.',
        ],
        'users.lock_key_reset' => [
            'name' => 'Users - Lock Key Reset Notification',
            'subject' => 'Lock key sıfırlandı',
            'body' => 'Yetkili bir kullanıcı oturum kilitleme keyinizi sıfırladı ve özelliği pasif yaptı.',
        ],
        'backup.completed' => [
            'name' => 'Backup - Completed Notification',
            'subject' => 'Backup tamamlandı',
            'body' => '{{label}} backup işlemi tamamlandı. Dosya: {{file_name}}',
        ],
        'ai.schema_synced' => [
            'name' => 'AI - Schema Synced Notification',
            'subject' => 'AI schema registry güncellendi',
            'body' => '{{entity_count}} entity ve {{field_count}} field senkronize edildi.',
        ],
        'queue.job_failed' => [
            'name' => 'Queue - Job Failed Notification',
            'subject' => 'Queue job başarısız oldu',
            'body' => '{{job_type}} job çalışırken hata oluştu: {{error_message}}',
        ],
    ];
}

function kirpi_template_sync_notification_defaults(): void
{
    $language = strtolower((string) env('APP_LOCALE', 'tr'));
    $language = $language !== '' ? $language : 'tr';

    foreach (kirpi_template_default_notification_templates() as $templateKey => $template) {
        $key = kirpi_template_normalize_code((string) $templateKey);
        if ($key === '') {
            continue;
        }

        kirpi_template_upsert_system(
            'content',
            kirpi_template_kind_module_for_key($key),
            $key,
            $key,
            (string) ($template['name'] ?? $key),
            $language,
            (string) ($template['subject'] ?? ''),
            (string) ($template['body'] ?? ''),
            kirpi_template_variables_for_target($key)
        );
    }
}

function kirpi_template_find_content_template(string $templateKey, bool $mustBeActive = true): ?array
{
    if (!kirpi_templates_table_ready()) {
        return null;
    }

    $templateKey = kirpi_template_normalize_code($templateKey);
    if ($templateKey === '') {
        return null;
    }

    $language = strtolower((string) env('APP_LOCALE', 'tr'));
    $language = $language !== '' ? $language : 'tr';

    try {
        $sql = "
            SELECT id, code, name, subject, body, is_active, is_system, updated_at
            FROM templates
            WHERE kind = 'content'
              AND language = :language
              AND (code = :template_key OR target_key = :template_key)
        ";
        if ($mustBeActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY code = :template_key_sort DESC LIMIT 1";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':language' => $language,
            ':template_key' => $templateKey,
            ':template_key_sort' => $templateKey,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        error_log('template content lookup error: ' . $e->getMessage());
        return null;
    }
}

function kirpi_template_find_active(string $kind, string $moduleKey, string $targetKey, string $code, string $language = 'tr'): ?array
{
    if (!kirpi_templates_table_ready()) {
        return null;
    }

    try {
        $stmt = db()->prepare("
            SELECT *
            FROM templates
            WHERE kind = :kind
              AND module_key = :module_key
              AND target_key = :target_key
              AND code = :code
              AND language = :language
              AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':kind' => $kind,
            ':module_key' => $moduleKey,
            ':target_key' => $targetKey,
            ':code' => $code,
            ':language' => $language,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        error_log('template find error: ' . $e->getMessage());
        return null;
    }
}

function kirpi_template_upsert_system(
    string $kind,
    string $moduleKey,
    string $targetKey,
    string $code,
    string $name,
    string $language,
    ?string $subject,
    string $body,
    array $variables = []
): void {
    if (!kirpi_templates_table_ready()) {
        return;
    }

    $variablesJson = json_encode(kirpi_template_normalize_variables($variables), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    try {
        $stmt = db()->prepare("
            INSERT INTO templates (
                kind, module_key, target_key, code, name, language, subject, body, variables_json, is_system, is_active
            ) VALUES (
                :kind, :module_key, :target_key, :code, :name, :language, :subject, :body, :variables_json, 1, 1
            )
            ON DUPLICATE KEY UPDATE
                is_system = 1,
                variables_json = VALUES(variables_json),
                updated_at = updated_at
        ");
        $stmt->execute([
            ':kind' => $kind,
            ':module_key' => $moduleKey,
            ':target_key' => $targetKey,
            ':code' => $code,
            ':name' => $name,
            ':language' => $language,
            ':subject' => $subject,
            ':body' => $body,
            ':variables_json' => $variablesJson ?: null,
        ]);
    } catch (Throwable $e) {
        error_log('template system upsert error: ' . $e->getMessage());
    }
}

function kirpi_template_sync_mail_defaults(array $defaults): void
{
    $language = strtolower((string) env('APP_LOCALE', 'tr'));
    $language = $language !== '' ? $language : 'tr';

    foreach ($defaults as $templateKey => $template) {
        $key = kirpi_template_normalize_code((string) $templateKey);
        if ($key === '') {
            continue;
        }

        kirpi_template_upsert_system(
            'email',
            kirpi_template_kind_module_for_key($key),
            $key,
            $key,
            (string) ($template['name'] ?? $key),
            $language,
            (string) ($template['subject'] ?? ''),
            (string) ($template['html_body'] ?? ''),
            kirpi_template_variables_for_target($key)
        );
    }
}

function kirpi_template_find_mail_template(string $templateKey, bool $mustBeActive = true): ?array
{
    if (!kirpi_templates_table_ready()) {
        return null;
    }

    $templateKey = kirpi_template_normalize_code($templateKey);
    if ($templateKey === '') {
        return null;
    }

    $language = strtolower((string) env('APP_LOCALE', 'tr'));
    $language = $language !== '' ? $language : 'tr';

    try {
        $sql = "
            SELECT id, code, name, subject, body, is_active, is_system, updated_at
            FROM templates
            WHERE kind = 'email'
              AND language = :language
              AND (code = :template_key OR target_key = :template_key)
        ";
        if ($mustBeActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY code = :template_key_sort DESC LIMIT 1";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':language' => $language,
            ':template_key' => $templateKey,
            ':template_key_sort' => $templateKey,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return [
            'id' => $row['id'] ?? null,
            'template_key' => (string) ($row['code'] ?? $templateKey),
            'name' => (string) ($row['name'] ?? $templateKey),
            'subject' => (string) ($row['subject'] ?? ''),
            'html_body' => (string) ($row['body'] ?? ''),
            'is_active' => (int) ($row['is_active'] ?? 0),
            'is_system' => (int) ($row['is_system'] ?? 0),
            'updated_at' => $row['updated_at'] ?? null,
        ];
    } catch (Throwable $e) {
        error_log('template mail lookup error: ' . $e->getMessage());
        return null;
    }
}
