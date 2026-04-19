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
    CONSTRAINT fk_mail_logs_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
