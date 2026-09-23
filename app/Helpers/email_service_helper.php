<?php

use App\Models\EmailSettingsModel;

if (!function_exists('sendSystemEmail')) {
    /**
     * Common helper to send system emails
     *
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param array|string $attachments Array of absolute file paths to attach or single file path
     * @return bool
     */
    function sendSystemEmail($to, $subject, $message, $attachments = [], $cc = [], $bcc = [], &$error = null)
    {
        $toStr = is_array($to) ? implode(',', $to) : $to;
        log_message('info', "Attempting to send email to {$toStr} with subject: {$subject}");

        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();

        if (!$settings || $settings['email_enabled'] == 0) {
            log_message('info', 'Email notification is disabled by administrator. Skipped sending email to ' . (is_array($to) ? implode(',', $to) : $to));
            return false;
        }

        $email = \Config\Services::email();
        $email->clear(true);

        // Load SMTP Configuration
        $config = [
            'protocol'   => 'smtp',
            'SMTPHost'   => $settings['smtp_host'] ?? '',
            'SMTPUser'   => $settings['smtp_username'] ?? '',
            'SMTPPort'   => $settings['smtp_port'] ?? '',
            'SMTPCrypto' => strtolower($settings['smtp_encryption'] ?? ''),
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'wordWrap'   => true,
            'CRLF'       => "\r\n",
            'newline'    => "\r\n"
        ];

        // Use password directly as it's saved in plain text
        $smtpPass = $settings['smtp_password'] ?? '';
        $config['SMTPPass'] = $smtpPass;

        $email->initialize($config);

        $fromEmail = !empty($settings['from_email']) ? $settings['from_email'] : 'noreply@example.com';
        $fromName  = !empty($settings['from_name']) ? $settings['from_name'] : 'System Administrator';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);
        
        if (!empty($cc)) {
            $email->setCC($cc);
        }
        if (!empty($bcc)) {
            $email->setBCC($bcc);
        }

        if (!empty($settings['reply_to_email'])) {
            $email->setReplyTo($settings['reply_to_email'], $fromName);
        }

        $email->setSubject($subject);
        $email->setMessage($message);

        if (!empty($attachments)) {
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
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
                $error = $dbg;
                log_message('error', "Email failed to send to {$toStr}. Error details:\n" . $dbg);
                return false;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            log_message('error', "Exception occurred while sending email to {$toStr}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
}
