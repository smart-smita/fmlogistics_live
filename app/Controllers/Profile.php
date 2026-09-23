<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Profile extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $user_id = session()->get('user_id');

        if (!$user_id) {
            return redirect()->to(base_url('Login'));
        }

        $query = "SELECT 
            user_id,
            user_emp_country,
            user_emp_zone,
            user_location,
            user_cluster,
            user_region,
            user_emp_code,
            user_name,
            user_email,
            user_contact,
            user_designation,
            employee_reporting_to,
            status,
            last_date,
            default_date,
            update_date
        FROM alert_users 
        WHERE user_id = ?";

        $data['user'] = $db->query($query, [$user_id])->getRowArray();

        return view('Profile/profile_view', $data);
    }

    public function updatePassword()
    {
        $request = service('request');
        $db = db_connect();
        $user_id = session()->get('user_id');

        if (!$user_id) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Not logged in.']);
        }

        $current_password = $request->getVar('current_password');
        $new_password = $request->getVar('new_password');
        $confirm_password = $request->getVar('confirm_password');

        if (strlen($new_password) < 6) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Password must be at least 6 characters.']);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/', $new_password)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Password format should be e.g. Abc@123 (Min 6 chars, uppercase, lowercase, number, special char).']);
        }

        if ($new_password !== $confirm_password) {
            return $this->response->setJSON(['status' => 0, 'message' => 'New Password and Confirm Password do not match.']);
        }

        if ($new_password === $current_password) {
            return $this->response->setJSON(['status' => 0, 'message' => 'New Password cannot be the same as Current Password.']);
        }

        $user = $db->query("SELECT user_password FROM alert_users WHERE user_id = ?", [$user_id])->getRowArray();

        if (!$user) {
            return $this->response->setJSON(['status' => 0, 'message' => 'User not found.']);
        }

        $is_valid = false;
        if ($user['user_password'] === $current_password) {
            $is_valid = true;
        } else if (password_verify($current_password, $user['user_password'])) {
            $is_valid = true;
        }

        if (!$is_valid) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Current password is wrong.']);
        }

        // Save password as it is (plain text)
        $updateQuery = "UPDATE alert_users SET user_password = ?, update_date = NOW() WHERE user_id = ?";
        $db->query($updateQuery, [$new_password, $user_id]);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Password updated successfully.',
            'redirect' => base_url('Login/log_out')
        ]);
    }
}
