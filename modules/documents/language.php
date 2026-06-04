<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

function documents_lang(string $key, ?string $default = null): string
{
    static $dictionary = [
        'tr' => [
            'documents' => 'Belgeler',
            'page_title' => 'Belge Yönetimi',
            'page_hint' => 'Sistem genelindeki dosya ve ek kayıtları merkezi olarak yönetilir.',
            'upload_document' => 'Belge Yükle',
            'document_list' => 'Belge Listesi',
            'filters' => 'Filtreler',
            'search' => 'Arama',
            'search_placeholder' => 'Dosya adı, MIME veya yol ara...',
            'filter' => 'Filtrele',
            'clear' => 'Temizle',
            'all_document_types' => 'Tüm Belge Türleri',
            'all_entity_types' => 'Tüm Entity Türleri',
            'document_type' => 'Belge Türü',
            'file' => 'Dosya',
            'entity_type' => 'Entity Türü',
            'entity_id' => 'Entity ID',
            'relation_type' => 'İlişki Türü',
            'original_name' => 'Orijinal Ad',
            'mime_type' => 'MIME',
            'file_size' => 'Boyut',
            'uploaded_by' => 'Yükleyen',
            'created_at' => 'Tarih',
            'actions' => 'İşlemler',
            'download' => 'İndir',
            'delete' => 'Sil',
            'save' => 'Yükle',
            'no_records' => 'Henüz belge kaydı yok.',
            'tables_missing' => 'Belge tabloları henüz kurulu değil.',
            'csrf_failed' => 'Güvenlik doğrulaması başarısız oldu.',
            'upload_success' => 'Belge yüklendi.',
            'upload_error' => 'Belge yüklenirken bir hata oluştu.',
            'delete_success' => 'Belge silindi.',
            'delete_error' => 'Belge silinirken bir hata oluştu.',
            'not_found' => 'Belge bulunamadı.',
            'file_not_found' => 'Belge dosyası bulunamadı.',
            'type_hint' => 'Örnek: attachment, report, user_document',
            'entity_hint' => 'Opsiyonel. Bir kayıtla ilişkilendirmek için doldurun.',
            'export_csv' => 'CSV',
            'export_excel' => 'Excel',
        ],
        'en' => [
            'documents' => 'Documents',
            'page_title' => 'Document Management',
            'page_hint' => 'System-wide files and attachments are managed centrally.',
            'upload_document' => 'Upload Document',
            'document_list' => 'Document List',
            'filters' => 'Filters',
            'search' => 'Search',
            'search_placeholder' => 'Search file name, MIME, or path...',
            'filter' => 'Filter',
            'clear' => 'Clear',
            'all_document_types' => 'All Document Types',
            'all_entity_types' => 'All Entity Types',
            'document_type' => 'Document Type',
            'file' => 'File',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
            'relation_type' => 'Relation Type',
            'original_name' => 'Original Name',
            'mime_type' => 'MIME',
            'file_size' => 'Size',
            'uploaded_by' => 'Uploaded By',
            'created_at' => 'Date',
            'actions' => 'Actions',
            'download' => 'Download',
            'delete' => 'Delete',
            'save' => 'Upload',
            'no_records' => 'No documents yet.',
            'tables_missing' => 'Document tables are not installed yet.',
            'csrf_failed' => 'Security validation failed.',
            'upload_success' => 'Document uploaded.',
            'upload_error' => 'An error occurred while uploading document.',
            'delete_success' => 'Document deleted.',
            'delete_error' => 'An error occurred while deleting document.',
            'not_found' => 'Document not found.',
            'file_not_found' => 'Document file not found.',
            'type_hint' => 'Example: attachment, report, user_document',
            'entity_hint' => 'Optional. Fill to link the file to an entity.',
            'export_csv' => 'CSV',
            'export_excel' => 'Excel',
        ],
    ];

    $locale = strtolower((string) env('APP_LOCALE', 'tr'));
    if (!isset($dictionary[$locale])) {
        $locale = 'tr';
    }

    return $dictionary[$locale][$key] ?? $dictionary['tr'][$key] ?? $default ?? $key;
}
