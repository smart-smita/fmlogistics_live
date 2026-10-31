<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmailSettingsModel;

class EmailSettings extends BaseController
{
    public function index()
    {
        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();

        // Never expose the plain text password in the view
        if (!empty($settings) && isset($settings['smtp_password'])) {
            $settings['has_password'] = !empty($settings['smtp_password']);
            $settings['smtp_password'] = '';
        }

        $data['settings'] = $settings;

        return view('Admin/email_settings', $data);
    }

    public function save()
    {
        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();

        // Get post data
        $postData = $this->request->getVar();

        $smtpHost = trim($postData['smtp_host'] ?? '');
        $smtpPort = trim($postData['smtp_port'] ?? '');
        $smtpUser = trim($postData['smtp_username'] ?? '');
        $smtpEncryption = strtolower(trim($postData['smtp_encryption'] ?? ''));
        $fromEmail = trim($postData['from_email'] ?? '');
        $fromName = trim($postData['from_name'] ?? '');
        $replyToEmail = trim($postData['reply_to_email'] ?? '');

        // Prepare data for update/insert
        $saveData = [
            'email_enabled'   => isset($postData['email_enabled']) ? 1 : 0,
            'smtp_host'       => $smtpHost,
            'smtp_port'       => $smtpPort,
            'smtp_username'   => $smtpUser,
            'smtp_encryption' => $smtpEncryption,
            'from_email'      => $fromEmail,
            'from_name'       => $fromName,
            'reply_to_email'  => $replyToEmail,
        ];

        // Only update password if a new one is provided
        if (!empty($postData['smtp_password'])) {
            $rawPass = trim($postData['smtp_password']);
            // Strip spaces if user pasted 16-character Google App Password with 3 spaces (e.g. "abcd efgh ijkl mnop")
            if (stripos($smtpHost, 'gmail') !== false && strlen($rawPass) === 19 && substr_count($rawPass, ' ') === 3) {
                $rawPass = str_replace(' ', '', $rawPass);
            }
            $saveData['smtp_password'] = $rawPass;
        }

        $session = session();
        $userId = $session->get('user_id') ?? 0;
        $saveData['updated_by'] = $userId;
        $saveData['updated_at'] = date('Y-m-d H:i:s');

        if ($settings) {
            $settingsModel->update($settings['id'], $saveData);
        } else {
            $saveData['created_by'] = $userId;
            $saveData['created_at'] = date('Y-m-d H:i:s');
            $settingsModel->insert($saveData);
        }

        return redirect()->to('admin/settings/email-settings')->with('success', 'Email settings updated successfully.');
    }

    public function testEmail()
    {
        $toEmail = trim($this->request->getVar('test_email') ?? '');
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Please provide a valid test email address.');
        }

        helper('email_service');

        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();
        $id = $settings ? $settings['id'] : null;

        if (!$settings || empty($settings['email_enabled'])) {
            return redirect()->back()->with('error', 'Email notifications are disabled. Please enable "Email Notifications" above and save settings first.');
        }

        if (empty($settings['smtp_host']) || empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
            return redirect()->back()->with('error', 'SMTP settings are incomplete. Please provide SMTP Host, Username, and Password.');
        }

        $subject = 'ALERT SMTP Connection Test';
        $message = '<h3>Hello,</h3><p>This is a test email to verify your SMTP configuration in ALERT. If you received this, your email settings are working correctly.</p><p><small>Timestamp: ' . date('Y-m-d H:i:s') . '</small></p>';

        $errorMsg = null;
        if (sendSystemEmail($toEmail, $subject, $message, [], [], [], $errorMsg)) {
            if ($id) {
                $settingsModel->update($id, [
                    'smtp_status' => 'Connected',
                    'last_test_email_date' => date('Y-m-d H:i:s')
                ]);
            }
            return redirect()->back()->with('success', 'Test email sent successfully to ' . $toEmail);
        } else {
            if ($id) {
                $settingsModel->update($id, [
                    'smtp_status' => 'Failed',
                    'last_test_email_date' => date('Y-m-d H:i:s')
                ]);
            }
            $displayError = parseSmtpError($errorMsg, $settings);
            return redirect()->back()->with('error', 'Failed to send test email. ' . $displayError);
        }
    }
}
