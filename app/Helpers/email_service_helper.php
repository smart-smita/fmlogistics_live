<?php

use App\Models\EmailSettingsModel;

if (!function_exists('sendSystemEmail')) {
    /**
     * Common helper to send system emails
     *
     * @param string|array $to
     * @param string       $subject
     * @param string       $message
     * @param array|string $attachments Array of absolute file paths to attach or single file path
     * @param array|string $cc
     * @param array|string $bcc
     * @param string|null  $error Output parameter for error message/debugger
     * @return bool
     */
    function sendSystemEmail($to, $subject, $message, $attachments = [], $cc = [], $bcc = [], &$error = null)
    {
        $toStr = is_array($to) ? implode(',', $to) : $to;
        log_message('info', "Attempting to send email to {$toStr} with subject: {$subject}");

        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();

        if (!$settings || empty($settings['email_enabled'])) {
            $msg = 'Email notification is disabled by administrator. Skipped sending email to ' . $toStr;
            log_message('info', $msg);
            $error = $msg;
            return false;
        }

        $email = \Config\Services::email();
        $email->clear(true);

        // Normalize Host & Port
        $host = trim($settings['smtp_host'] ?? 'smtp.gmail.com');
        $port = !empty($settings['smtp_port']) ? (int)$settings['smtp_port'] : 465;

        // Normalize Encryption
        $crypto = strtolower(trim($settings['smtp_encryption'] ?? ''));
        if (empty($crypto)) {
            $crypto = ($port === 465) ? 'ssl' : (($port === 587) ? 'tls' : '');
        }

        // Clean & Normalize Password (strip spaces if 19-char 3-space Google App Password format)
        $smtpPass = trim($settings['smtp_password'] ?? '');
        if (stripos($host, 'gmail') !== false && strlen($smtpPass) === 19 && substr_count($smtpPass, ' ') === 3) {
            $smtpPass = str_replace(' ', '', $smtpPass);
        }

        // CodeIgniter 4 SMTP Configuration
        $config = [
            'protocol'    => 'smtp',
            'SMTPHost'    => $host,
            'SMTPUser'    => trim($settings['smtp_username'] ?? ''),
            'SMTPPass'    => $smtpPass,
            'SMTPPort'    => $port,
            'SMTPCrypto'  => $crypto,
            'SMTPTimeout' => 30,
            'mailType'    => 'html',
            'charset'     => 'utf-8',
            'wordWrap'    => true,
            'CRLF'        => "\r\n",
            'newline'     => "\r\n"
        ];

        $email->initialize($config);

        $fromEmail = !empty($settings['from_email']) ? trim($settings['from_email']) : 'noreply@example.com';
        $fromName  = !empty($settings['from_name']) ? trim($settings['from_name']) : 'ALERT System';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);

        if (!empty($cc)) {
            $email->setCC($cc);
        }
        if (!empty($bcc)) {
            $email->setBCC($bcc);
        }

        if (!empty($settings['reply_to_email'])) {
            $email->setReplyTo(trim($settings['reply_to_email']), $fromName);
        }

        $email->setSubject($subject);
        $email->setMessage($message);

        if (!empty($attachments)) {
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
            foreach ($attachments as $attachment) {
                if (!empty($attachment) && file_exists($attachment)) {
                    $email->attach($attachment);
                }
            }
        }

        try {
            if ($email->send()) {
                log_message('info', "Email sent successfully to {$toStr}");
                return true;
            } else {
                $dbg = $email->printDebugger(['headers']);
                // Sanitize any accidental password in debugger string
                if (!empty($smtpPass)) {
                    $dbg = str_replace($smtpPass, '********', $dbg);
                }
                $error = $dbg;
                log_message('error', "Email failed to send to {$toStr}. Diagnostic: " . strip_tags($dbg));
                return false;
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            log_message('error', "Exception sending email to {$toStr}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('parseSmtpError')) {
    /**
     * Parses raw SMTP debugger output into a clear, administrator-facing diagnostic message
     *
     * @param string|null $rawError
     * @param array|null  $settings
     * @return string
     */
    function parseSmtpError($rawError, $settings = [])
    {
        if (empty($rawError)) {
            return 'Unable to establish SMTP connection. Please check host and port settings.';
        }

        $host  = $settings['smtp_host'] ?? 'smtp.gmail.com';
        $port  = !empty($settings['smtp_port']) ? $settings['smtp_port'] : '465';
        $lower = strtolower($rawError);

        // 1. Authentication Failure (Bad Credentials)
        if (strpos($lower, 'failed to authenticate password') !== false || strpos($lower, '535') !== false || strpos($lower, 'badcredentials') !== false) {
            return "SMTP Authentication failed (535 Bad Credentials). Google rejected the username or App Password. Ensure 2-Step Verification is ON for {$settings['smtp_username']} and enter a valid 16-character App Password.";
        }

        // 2. DNS Resolution Error
        if (strpos($lower, 'getaddrinfo') !== false || strpos($lower, 'nodename nor servname') !== false) {
            return "DNS lookup failed for host '{$host}'. The mail server address could not be resolved.";
        }

        // 3. Socket Timeout / Connection Blocked by Host Firewall
        if (strpos($lower, 'timed out') !== false || strpos($lower, 'operation timed out') !== false || strpos($lower, 'connection refused') !== false || strpos($lower, 'socket_error') !== false || (strpos($lower, 'unable to send email using php smtp') !== false && strpos($lower, 'hello:') === false)) {
            return "Connection timed out connecting to {$host}:{$port}. The mail server could not be reached. On GoDaddy shared cPanel hosting, outbound connections on ports 465 and 587 are blocked by default. You must request GoDaddy Support to unblock outbound SMTP to {$host}, or configure an alternative hosting-confirmed relay.";
        }

        // 4. SSL/TLS Negotiation Error
        if (strpos($lower, 'wrong version number') !== false || strpos($lower, 'certificate') !== false || strpos($lower, 'tls_handshake_error') !== false || strpos($lower, 'ssl operation failed') !== false) {
            return "SSL/TLS Handshake failed on {$host}:{$port}. Unable to negotiate secure cipher protocol. Verify that OpenSSL supports TLS 1.2+.";
        }

        // 5. Default Clean Message
        $clean = strip_tags($rawError);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return trim($clean);
    }
}
