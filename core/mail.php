<?php

function kirpi_mail_setting(string $key, string $fallback = ''): string
{
    if (function_exists('kirpi_setting_get')) {
        $value = trim((string) kirpi_setting_get($key, ''));
        if ($value !== '') {
            return $value;
        }
    }

    return trim($fallback);
}

function kirpi_mail_from_address(): string
{
    $from = kirpi_mail_setting('mail.from_address', (string) MAIL_FROM_ADDRESS);

    if ($from !== '') {
        return $from;
    }

    $username = kirpi_mail_setting('mail.username', (string) MAIL_USERNAME);
    if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
        return $username;
    }

    return 'no-reply@localhost';
}

function kirpi_mail_from_name(): string
{
    $name = kirpi_mail_setting('mail.from_name', (string) MAIL_FROM_NAME);
    return $name !== '' ? $name : APP_NAME;
}

function kirpi_mail_uses_smtp(): bool
{
    return kirpi_mail_setting('mail.host', (string) MAIL_HOST) !== '';
}

function kirpi_mail_config_status(): array
{
    $status = [
        'transport' => kirpi_mail_uses_smtp() ? 'smtp' : 'php_mail',
        'mail_host' => kirpi_mail_setting('mail.host', (string) MAIL_HOST) !== '',
        'mail_port' => (int) kirpi_mail_setting('mail.port', (string) MAIL_PORT) > 0,
        'mail_username' => kirpi_mail_setting('mail.username', (string) MAIL_USERNAME) !== '',
        'mail_password' => kirpi_mail_setting('mail.password', (string) MAIL_PASSWORD) !== '',
        'mail_from_address' => kirpi_mail_setting('mail.from_address', (string) MAIL_FROM_ADDRESS) !== '',
        'mail_from_name' => kirpi_mail_setting('mail.from_name', (string) MAIL_FROM_NAME) !== '',
        'mail_encryption' => in_array(strtolower(kirpi_mail_setting('mail.encryption', (string) MAIL_ENCRYPTION)), ['tls', 'ssl', 'none', ''], true),
    ];

    $status['ready'] = $status['transport'] === 'php_mail'
        ? $status['mail_from_address'] || trim((string) MAIL_USERNAME) !== ''
        : $status['mail_host'] && $status['mail_port'] && $status['mail_from_address'];

    return $status;
}

function kirpi_mail_log(array $payload): void
{
    if (!db_table_exists('mail_logs')) {
        return;
    }

    try {
        $stmt = db()->prepare("\n            INSERT INTO mail_logs (\n                user_id, recipient_email, subject, body_preview, transport, status, error_message\n            ) VALUES (\n                :user_id, :recipient_email, :subject, :body_preview, :transport, :status, :error_message\n            )\n        ");

        $stmt->execute([
            ':user_id' => $payload['user_id'] ?? null,
            ':recipient_email' => (string) ($payload['recipient_email'] ?? ''),
            ':subject' => (string) ($payload['subject'] ?? ''),
            ':body_preview' => (string) ($payload['body_preview'] ?? ''),
            ':transport' => (string) ($payload['transport'] ?? 'unknown'),
            ':status' => (string) ($payload['status'] ?? 'failed'),
            ':error_message' => $payload['error_message'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('mail log insert error: ' . $e->getMessage());
    }
}

function kirpi_smtp_read($socket): string
{
    $response = '';

    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }

        $response .= $line;

        if (strlen($line) < 4) {
            break;
        }

        if (preg_match('/^[0-9]{3} /', $line) === 1) {
            break;
        }
    }

    return $response;
}

function kirpi_smtp_command($socket, string $command, array $expectedCodes): array
{
    fwrite($socket, $command . "\r\n");
    $response = kirpi_smtp_read($socket);

    $code = 0;
    if (preg_match('/^([0-9]{3})/m', $response, $matches) === 1) {
        $code = (int) $matches[1];
    }

    return [
        'ok' => in_array($code, $expectedCodes, true),
        'code' => $code,
        'response' => trim($response),
    ];
}

function kirpi_smtp_send_mail(string $to, string $subject, string $htmlBody): array
{
    $host = kirpi_mail_setting('mail.host', (string) MAIL_HOST);
    $port = (int) kirpi_mail_setting('mail.port', (string) MAIL_PORT);
    $username = kirpi_mail_setting('mail.username', (string) MAIL_USERNAME);
    $password = kirpi_mail_setting('mail.password', (string) MAIL_PASSWORD);
    $encryption = strtolower(kirpi_mail_setting('mail.encryption', (string) MAIL_ENCRYPTION));
    $fromAddress = kirpi_mail_from_address();
    $fromName = kirpi_mail_from_name();

    if ($host === '' || $port <= 0) {
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'SMTP ayarlari eksik: MAIL_HOST veya MAIL_PORT.',
        ];
    }

    if ($encryption === '' || $encryption === 'none') {
        $remote = "tcp://{$host}:{$port}";
    } elseif ($encryption === 'ssl') {
        $remote = "ssl://{$host}:{$port}";
    } else {
        $remote = "tcp://{$host}:{$port}";
    }

    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'SMTP baglantisi kurulamadi: ' . ($errstr !== '' ? $errstr : 'unknown error'),
        ];
    }

    stream_set_timeout($socket, 15);

    $greeting = kirpi_smtp_read($socket);
    if (strpos($greeting, '220') !== 0) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'SMTP acilis yaniti gecersiz: ' . trim($greeting),
        ];
    }

    $localHost = parse_url(BASE_URL, PHP_URL_HOST) ?: 'localhost';
    $ehlo = kirpi_smtp_command($socket, 'EHLO ' . $localHost, [250]);
    if (!$ehlo['ok']) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'EHLO basarisiz: ' . $ehlo['response'],
        ];
    }

    if ($encryption === 'tls') {
        $startTls = kirpi_smtp_command($socket, 'STARTTLS', [220]);
        if (!$startTls['ok']) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'STARTTLS basarisiz: ' . $startTls['response'],
            ];
        }

        $cryptoOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($cryptoOk !== true) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'TLS sifreli kanal baslatilamadi.',
            ];
        }

        $ehloAfterTls = kirpi_smtp_command($socket, 'EHLO ' . $localHost, [250]);
        if (!$ehloAfterTls['ok']) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'TLS sonrasi EHLO basarisiz: ' . $ehloAfterTls['response'],
            ];
        }
    }

    if ($username !== '') {
        $auth = kirpi_smtp_command($socket, 'AUTH LOGIN', [334]);
        if (!$auth['ok']) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'SMTP AUTH LOGIN basarisiz: ' . $auth['response'],
            ];
        }

        $userResp = kirpi_smtp_command($socket, base64_encode($username), [334]);
        if (!$userResp['ok']) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'SMTP kullanici dogrulamasi basarisiz: ' . $userResp['response'],
            ];
        }

        $passResp = kirpi_smtp_command($socket, base64_encode($password), [235]);
        if (!$passResp['ok']) {
            fclose($socket);
            return [
                'success' => false,
                'transport' => 'smtp',
                'error' => 'SMTP parola dogrulamasi basarisiz: ' . $passResp['response'],
            ];
        }
    }

    $mailFrom = kirpi_smtp_command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
    if (!$mailFrom['ok']) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'MAIL FROM basarisiz: ' . $mailFrom['response'],
        ];
    }

    $rcptTo = kirpi_smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
    if (!$rcptTo['ok']) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'RCPT TO basarisiz: ' . $rcptTo['response'],
        ];
    }

    $dataCommand = kirpi_smtp_command($socket, 'DATA', [354]);
    if (!$dataCommand['ok']) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'DATA komutu basarisiz: ' . $dataCommand['response'],
        ];
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [];
    $headers[] = 'Date: ' . date('r');
    $headers[] = 'From: ' . kirpi_mail_header_name($fromName) . ' <' . $fromAddress . '>';
    $headers[] = 'To: <' . $to . '>';
    $headers[] = 'Subject: ' . $encodedSubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $body = str_replace(["\r\n", "\r"], "\n", $htmlBody);
    $body = preg_replace('/^\./m', '..', $body ?? '');

    $messageData = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $body) . "\r\n.";
    fwrite($socket, $messageData . "\r\n");

    $dataResponse = kirpi_smtp_read($socket);
    if (strpos($dataResponse, '250') !== 0) {
        fclose($socket);
        return [
            'success' => false,
            'transport' => 'smtp',
            'error' => 'Mesaj teslimi basarisiz: ' . trim($dataResponse),
        ];
    }

    kirpi_smtp_command($socket, 'QUIT', [221, 250]);
    fclose($socket);

    return [
        'success' => true,
        'transport' => 'smtp',
    ];
}

function kirpi_mail_header_name(string $name): string
{
    $trimmed = trim($name);
    if ($trimmed === '') {
        return APP_NAME;
    }

    if (preg_match('/[^\x20-\x7E]/', $trimmed) === 1) {
        return '=?UTF-8?B?' . base64_encode($trimmed) . '?=';
    }

    return str_replace(['\r', '\n'], '', $trimmed);
}

function kirpi_php_mail_send(string $to, string $subject, string $htmlBody): array
{
    $fromAddress = kirpi_mail_from_address();
    $fromName = kirpi_mail_header_name(kirpi_mail_from_name());

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $fromName . ' <' . $fromAddress . '>';

    $ok = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));

    if (!$ok) {
        return [
            'success' => false,
            'transport' => 'php_mail',
            'error' => 'PHP mail() gonderimi basarisiz oldu.',
        ];
    }

    return [
        'success' => true,
        'transport' => 'php_mail',
    ];
}

function kirpi_send_mail(string $to, string $subject, string $htmlBody, ?int $userId = null): array
{
    $to = trim($to);
    $subject = trim($subject);

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Gecerli bir alici e-posta adresi girin.',
            'transport' => 'none',
        ];
    }

    if ($subject === '') {
        return [
            'success' => false,
            'message' => 'Konu bos olamaz.',
            'transport' => 'none',
        ];
    }

    $result = kirpi_mail_uses_smtp()
        ? kirpi_smtp_send_mail($to, $subject, $htmlBody)
        : kirpi_php_mail_send($to, $subject, $htmlBody);

    kirpi_mail_log([
        'user_id' => $userId,
        'recipient_email' => $to,
        'subject' => $subject,
        'body_preview' => mb_substr(strip_tags($htmlBody), 0, 500),
        'transport' => $result['transport'] ?? 'unknown',
        'status' => ($result['success'] ?? false) ? 'sent' : 'failed',
        'error_message' => $result['error'] ?? null,
    ]);

    if (!($result['success'] ?? false)) {
        return [
            'success' => false,
            'message' => (string) ($result['error'] ?? 'Mail gonderilemedi.'),
            'transport' => (string) ($result['transport'] ?? 'unknown'),
        ];
    }

    return [
        'success' => true,
        'message' => 'Test e-postasi basariyla gonderildi.',
        'transport' => (string) ($result['transport'] ?? 'unknown'),
    ];
}
