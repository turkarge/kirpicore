CREATE TABLE IF NOT EXISTS mail_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_preview TEXT NULL,
    transport VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mail_logs_user_id (user_id),
    INDEX idx_mail_logs_status (status),
    INDEX idx_mail_logs_created_status_id (created_at, status, id),
    CONSTRAINT fk_mail_logs_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mail_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(120) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    html_body MEDIUMTEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mail_templates_active (is_active),
    INDEX idx_mail_templates_system (is_system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO mail_templates (template_key, name, subject, html_body, is_active, is_system)
VALUES
('auth.password_reset', 'Auth - Password Reset', '{{app_name}} - Sifre Sifirlama', '<p>Merhaba {{user_name}},</p><p>Sifrenizi sifirlamak icin asagidaki baglantiyi kullanin:</p><p><a href="{{reset_link}}">{{reset_link}}</a></p><p>Bu baglanti {{expires_minutes}} dakika gecerlidir.</p>', 1, 1),
('queue.test_mail', 'Queue - Test Mail', '{{app_name}} Queue Test', '<p>Merhaba {{user_name}},</p><p>Bu e-posta kuyruk (queue) sistemi uzerinden gonderildi.</p><p>Tarih: {{sent_at}}</p>', 1, 1),
('mail.test_manual', 'Mail - Manual Test', '{{app_name}} Test Maili', '<p>{{{message_html}}}</p>', 1, 1),
('users.session_dropped', 'Users - Session Dropped', '{{app_name}} - Oturum Sonlandirildi', '<p>Merhaba {{user_name}},</p><p>Oturumlariniz bir yonetici tarafindan sonlandirildi.</p><p>Lutfen yeniden giris yapin.</p>', 1, 1),
('users.lock_key_reset', 'Users - Lock Key Reset', '{{app_name}} - Kilit Key Sifirlandi', '<p>Merhaba {{user_name}},</p><p>Oturum kilitleme key bilginiz yonetici tarafindan sifirlandi.</p><p>Profil ekranindan yeni key olusturabilirsiniz.</p>', 1, 1);
