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

        // Pass settings to view
        $data['settings'] = $settings;

        return view('Admin/email_settings', $data);
    }

    public function save()
    {
        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();

        // Get post data
        $postData = $this->request->getVar();

        // Prepare data for update/insert
        $saveData = [
            'email_enabled' => isset($postData['email_enabled']) ? 1 : 0,
            'smtp_host' => $postData['smtp_host'] ?? '',
            'smtp_port' => $postData['smtp_port'] ?? '',
            'smtp_username' => $postData['smtp_username'] ?? '',
            'smtp_encryption' => $postData['smtp_encryption'] ?? '',
            'from_email' => $postData['from_email'] ?? '',
            'from_name' => $postData['from_name'] ?? '',
            'reply_to_email' => $postData['reply_to_email'] ?? '',
        ];

        // Only update password if a new one is provided
        if (!empty($postData['smtp_password'])) {
            $saveData['smtp_password'] = $postData['smtp_password'];
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
        $toEmail = $this->request->getVar('test_email');
        if (empty($toEmail)) {
            return redirect()->back()->with('error', 'Please provide a test email address.');
        }

        helper('email_service');

        $subject = 'SMTP Connection Test';
        $message = '<h3>Hello,</h3><p>This is a test email to verify your SMTP configuration. If you received this, your email settings are working correctly.</p>';

        $settingsModel = new EmailSettingsModel();
        $settings = $settingsModel->getSettings();
        $id = $settings ? $settings['id'] : null;

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
            $displayError = $errorMsg ? ' Error details: ' . strip_tags($errorMsg) : '';
            return redirect()->back()->with('error', 'Failed to send test email.' . $displayError);
        }
    }
}
