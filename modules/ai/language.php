<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function ai_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'system_management' => 'Sistem Yönetimi',
                'kirpi_intelligence' => 'Kirpi Intelligence',
                'subtitle' => 'Core AI altyapısı',
                'schema_registry' => 'Schema Registry',
                'schema_registry_detail' => 'Modül veri yapıları merkezi olarak burada yayınlanır.',
                'ai_audit_log' => 'AI Audit Log',
                'ai_audit_log_detail' => 'AI işlemleri hem genel audit hem de AI audit tablosuna yazılır.',
                'model_adapters' => 'Model Adapters',
                'model_adapters_detail' => 'Model seçimi ürünlerden ayrılır ve Core tarafından yönetilir.',
                'active_entities' => 'Aktif Entity',
                'active_fields' => 'Aktif Field',
                'audit_records' => 'Audit Kaydı',
                'adapter_count' => 'Adapter',
                'latest_entities' => 'Son Schema Entity Kayıtları',
                'module' => 'Modül',
                'entity' => 'Entity',
                'table' => 'Tablo',
                'fields' => 'Fields',
                'permission' => 'Yetki',
                'updated_at' => 'Güncelleme',
                'no_schema' => 'Henüz schema entity kaydı yok.',
                'schema_missing' => 'AI schema tabloları henüz kurulmamış.',
                'adapter_missing' => 'Model adapter tablosu henüz kurulmamış.',
                'status_ready' => 'Hazır',
                'status_missing' => 'Eksik',
                'read_only_notice' => 'İlk sürüm metadata-only ve read-only çalışacak şekilde hazırlandı.',
                'schema_manifests' => 'Schema Manifest',
                'sync_schema' => 'Schema Sync',
                'csrf_failed' => 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.',
                'schema_sync_success' => 'Schema registry güncellendi: %d entity, %d field.',
                'schema_sync_partial' => 'Schema registry kısmen güncellendi. Logları kontrol edin.',
                'schema_sync_error' => 'Schema registry güncellenemedi.',
            ],
            'en' => [
                'system_management' => 'System Management',
                'kirpi_intelligence' => 'Kirpi Intelligence',
                'subtitle' => 'Core AI infrastructure',
                'schema_registry' => 'Schema Registry',
                'schema_registry_detail' => 'Module data structures are published centrally here.',
                'ai_audit_log' => 'AI Audit Log',
                'ai_audit_log_detail' => 'AI operations are written to both general audit and AI audit tables.',
                'model_adapters' => 'Model Adapters',
                'model_adapters_detail' => 'Model selection is decoupled from products and managed by Core.',
                'active_entities' => 'Active Entities',
                'active_fields' => 'Active Fields',
                'audit_records' => 'Audit Records',
                'adapter_count' => 'Adapters',
                'latest_entities' => 'Latest Schema Entities',
                'module' => 'Module',
                'entity' => 'Entity',
                'table' => 'Table',
                'fields' => 'Fields',
                'permission' => 'Permission',
                'updated_at' => 'Updated At',
                'no_schema' => 'No schema entity has been registered yet.',
                'schema_missing' => 'AI schema tables are not installed yet.',
                'adapter_missing' => 'Model adapter table is not installed yet.',
                'status_ready' => 'Ready',
                'status_missing' => 'Missing',
                'read_only_notice' => 'The first version is prepared as metadata-only and read-only.',
                'schema_manifests' => 'Schema Manifests',
                'sync_schema' => 'Sync Schema',
                'csrf_failed' => 'Session validation failed. Refresh the page and try again.',
                'schema_sync_success' => 'Schema registry updated: %d entities, %d fields.',
                'schema_sync_partial' => 'Schema registry was partially updated. Check logs.',
                'schema_sync_error' => 'Schema registry could not be updated.',
            ],
        ];
    }

    $locale = strtolower((string) env('APP_LOCALE', 'tr'));
    if (!isset($dictionary[$locale])) {
        $locale = 'tr';
    }

    if (isset($dictionary[$locale][$key])) {
        return $dictionary[$locale][$key];
    }

    if (isset($dictionary['tr'][$key])) {
        return $dictionary['tr'][$key];
    }

    return $default ?? $key;
}
