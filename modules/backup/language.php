<?php

if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function backup_lang(string $key, ?string $default = null): string
{
    static $dictionary = null;

    if ($dictionary === null) {
        $dictionary = [
            'tr' => [
                'system_management' => 'Sistem Yonetimi',
                'backup_restore' => 'Backup / Restore',
                'backup_tables_missing' => 'Backup tablolari kurulu degil. Kurulum icin setup veya db:install calistirin.',
                'new_backup' => 'Yeni Backup',
                'label' => 'Etiket',
                'label_placeholder' => 'ornek: deploy_oncesi',
                'create_backup' => 'Backup Olustur',
                'recent_backups' => 'Son Backup Kayitlari',
                'file' => 'Dosya',
                'size' => 'Boyut',
                'status' => 'Durum',
                'date' => 'Tarih',
                'created_by' => 'Olusturan',
                'no_records' => 'Kayit bulunamadi.',
                'download' => 'Indir',
                'verify' => 'Dogrula',
                'restore' => 'Restore',
                'delete' => 'Sil',
                'verify_confirm' => 'Bu backup dosyasi checksum ve dry-run restore ile dogrulanacak. Emin misiniz?',
                'restore_confirm' => 'Bu backup geri yuklenecek. Emin misiniz?',
                'delete_confirm' => 'Bu backup kaydi silinecek. Emin misiniz?',
                'recent_restores' => 'Son Restore Loglari',
                'restored_by' => 'Restore Eden',
                'invalid_backup_record' => 'Gecersiz backup kaydi.',
                'table_not_ready' => 'Backup tablosu henuz kurulu degil.',
                'record_not_found' => 'Backup kaydi bulunamadi.',
                'file_path_invalid' => 'Backup dosya yolu gecersiz.',
                'file_not_found' => 'Backup dosyasi bulunamadi.',
                'download_error' => 'Backup indirilirken bir hata olustu.',
                'csrf_failed' => 'Guvenlik dogrulamasi basarisiz oldu.',
                'create_failed_default' => 'Backup olusturulamadi.',
                'delete_failed' => 'Backup silinirken bir hata olustu.',
                'restore_failed_default' => 'Restore islemi basarisiz.',
                'verify_failed_default' => 'Backup dogrulama basarisiz.',
                'delete_success' => 'Backup kaydi silindi.',
                'restore_success' => 'Restore komutu calistirildi.',
                'verify_success_default' => 'Backup dogrulandi.',
                'create_success_prefix' => 'Backup olusturuldu. ID: ',
                'retention_deleted_prefix' => ' Retention temizligi: ',
                'retention_deleted_suffix' => ' eski backup silindi.',
                'checksum_prefix' => ' SHA256: ',
                'dry_run_prefix' => ' Dry-run tablo: ',
                'dry_run_suffix' => '.',
            ],
            'en' => [
                'system_management' => 'System Management',
                'backup_restore' => 'Backup / Restore',
                'backup_tables_missing' => 'Backup tables are not installed. Run setup or db:install.',
                'new_backup' => 'New Backup',
                'label' => 'Label',
                'label_placeholder' => 'example: before_deploy',
                'create_backup' => 'Create Backup',
                'recent_backups' => 'Recent Backups',
                'file' => 'File',
                'size' => 'Size',
                'status' => 'Status',
                'date' => 'Date',
                'created_by' => 'Created By',
                'no_records' => 'No records found.',
                'download' => 'Download',
                'verify' => 'Verify',
                'restore' => 'Restore',
                'delete' => 'Delete',
                'verify_confirm' => 'This backup will be validated with checksum and dry-run restore. Continue?',
                'restore_confirm' => 'This backup will be restored. Continue?',
                'delete_confirm' => 'This backup record will be deleted. Continue?',
                'recent_restores' => 'Recent Restore Logs',
                'restored_by' => 'Restored By',
                'invalid_backup_record' => 'Invalid backup record.',
                'table_not_ready' => 'Backup table is not installed yet.',
                'record_not_found' => 'Backup record not found.',
                'file_path_invalid' => 'Backup file path is invalid.',
                'file_not_found' => 'Backup file not found.',
                'download_error' => 'An error occurred while downloading backup.',
                'csrf_failed' => 'Security validation failed.',
                'create_failed_default' => 'Backup could not be created.',
                'delete_failed' => 'An error occurred while deleting backup.',
                'restore_failed_default' => 'Restore process failed.',
                'verify_failed_default' => 'Backup verification failed.',
                'delete_success' => 'Backup record deleted.',
                'restore_success' => 'Restore command executed.',
                'verify_success_default' => 'Backup verified.',
                'create_success_prefix' => 'Backup created. ID: ',
                'retention_deleted_prefix' => ' Retention cleanup: ',
                'retention_deleted_suffix' => ' old backups deleted.',
                'checksum_prefix' => ' SHA256: ',
                'dry_run_prefix' => ' Dry-run tables: ',
                'dry_run_suffix' => '.',
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
