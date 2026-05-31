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
                'system_management' => 'Sistem Yonetimi',
                'kirpi_intelligence' => 'Kirpi Intelligence',
                'subtitle' => 'Core AI altyapisi',
                'schema_registry' => 'Schema Registry',
                'schema_registry_detail' => 'Modul veri yapilari merkezi olarak burada yayinlanir.',
                'ai_audit_log' => 'AI Audit Log',
                'ai_audit_log_detail' => 'AI islemleri hem genel audit hem de AI audit tablosuna yazilir.',
                'model_adapters' => 'Model Adapters',
                'model_adapters_detail' => 'Model secimi urunlerden ayrilir ve Core tarafindan yonetilir.',
                'active_entities' => 'Aktif Entity',
                'active_fields' => 'Aktif Field',
                'audit_records' => 'Audit Kaydi',
                'adapter_count' => 'Adapter',
                'latest_entities' => 'Son Schema Entity Kayitlari',
                'module' => 'Modul',
                'entity' => 'Entity',
                'table' => 'Tablo',
                'fields' => 'Fields',
                'permission' => 'Yetki',
                'updated_at' => 'Guncelleme',
                'no_schema' => 'Henuz schema entity kaydi yok.',
                'schema_missing' => 'AI schema tablolari henuz kurulmamis.',
                'adapter_missing' => 'Model adapter tablosu henuz kurulmamis.',
                'status_ready' => 'Hazir',
                'status_missing' => 'Eksik',
                'read_only_notice' => 'Ilk surum metadata-only ve read-only calisacak sekilde hazirlandi.',
                'schema_manifests' => 'Schema Manifest',
                'sync_schema' => 'Schema Sync',
                'csrf_failed' => 'Oturum dogrulamasi basarisiz. Sayfayi yenileyip tekrar deneyin.',
                'schema_sync_success' => 'Schema registry guncellendi: %d entity, %d field.',
                'schema_sync_partial' => 'Schema registry kismen guncellendi. Loglari kontrol edin.',
                'schema_sync_error' => 'Schema registry guncellenemedi.',
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
