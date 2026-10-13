<?php

if (!function_exists('resolveRecipients')) {
    /**
     * Resolves the email recipients based on a location string.
     * 
     * @param string $locationString
     * @return array Array of unique email addresses
     */
    function resolveRecipients($locationString)
    {
        $db = db_connect();
        $emails = [];

        // 1. Find the location in alert_location_master
        $location = $db->table('alert_location_master')
                       ->where('location_name', trim($locationString))
                       ->get()
                       ->getRowArray();

        $clusterName = '';
        $accountManager = '';

        if ($location) {
            $clusterName = $location['cluster_name'] ?? '';
            $accountManager = $location['account_manager'] ?? '';
        }

        // We need:
        // - Cluster Manager
        // - Account Manager
        // - Engineer (based on location)
        // - ALL Auditors
        // - ALL Higher Authority

        // Prepare conditions for querying alert_users
        $conditions = [];

        // Engineer for this location
        if (!empty(trim($locationString))) {
            $conditions[] = "(user_designation = 'Engineer' AND user_location = " . $db->escape(trim($locationString)) . ")";
        }

        // Cluster Manager
        if (!empty(trim($clusterName))) {
            $conditions[] = "(user_designation IN ('Cluster manager', 'Cluster Manager') AND user_cluster = " . $db->escape(trim($clusterName)) . ")";
        }

        // Account Manager (matched by name)
        if (!empty(trim($accountManager))) {
            $conditions[] = "(user_designation IN ('Account manager', 'Account Manager') AND user_name = " . $db->escape(trim($accountManager)) . ")";
        }

        // Auditors
        $conditions[] = "(user_designation = 'Auditor')";

        // Higher Authority
        $conditions[] = "(user_designation IN ('Higher authority', 'Higher Authority'))";

        if (empty($conditions)) {
            return [];
        }

        $whereClause = implode(' OR ', $conditions);
        
        $users = $db->table('alert_users')
                    ->where('status', 1)
                    ->where("($whereClause)", null, false)
                    ->get()
                    ->getResultArray();

        foreach ($users as $user) {
            if (!empty($user['user_email']) && filter_var(trim($user['user_email']), FILTER_VALIDATE_EMAIL)) {
                $emails[] = trim($user['user_email']);
            }
        }

        // Remove duplicates and re-index
        return array_values(array_unique($emails));
    }
}

if (!function_exists('sendNearMissNotification')) {
    /**
     * Triggers Near Miss notification email
     *
     * @param int $nearMissId
     * @param bool $isNew
     * @param int|null $oldStatus
     * @param int|null $newStatus
     */
    function sendNearMissNotification($nearMissId, $isNew, $oldStatus = null, $newStatus = null)
    {
        $db = db_connect();
        
        // Fetch near miss record
        $details = $db->query("
            SELECT alert_near_miss.*, 
                   IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as reported_by,
                   l.cluster_name, l.region_name, l.account_manager as location_am
            FROM alert_near_miss 
            LEFT JOIN alert_users ON alert_users.user_id = alert_near_miss.user_id 
            LEFT JOIN alert_location_master l ON l.location_name = alert_near_miss.location
            WHERE near_miss_id = ?
        ", [$nearMissId])->getRowArray();

        if (!$details) {
            log_message('error', "sendNearMissNotification: Near Miss ID $nearMissId not found.");
            return;
        }

        $locationString = $details['location'] ?? '';
        $recipients = resolveRecipients($locationString);

        if (empty($recipients)) {
            log_message('info', "sendNearMissNotification: No recipients found for Near Miss ID $nearMissId at location '$locationString'.");
            return;
        }

        $statusText = [
            0 => 'Pending',
            1 => 'Verified',
            2 => 'Open',
            3 => 'Cancel',
            4 => 'Request required',
            5 => 'Close'
        ];

        $currentStatusText = $statusText[$details['status']] ?? 'Unknown';
        
        if ($isNew) {
            $subject = "New Near Miss Report - {$locationString} - #{$nearMissId}";
        } else {
            $oldStatusStr = $statusText[$oldStatus] ?? 'Unknown';
            $newStatusStr = $statusText[$newStatus] ?? 'Unknown';
            $subject = "Near Miss Status Updated - {$locationString} - #{$nearMissId} - {$oldStatusStr} → {$newStatusStr}";
        }

        $baseUrl = base_url('Reports/Near_miss_reports/nearMissBrifDetails/' . $nearMissId);
        
        // Build email body
        $body = "<div style='font-family: Arial, sans-serif; color: #333;'>";
        if ($isNew) {
            $body .= "<h2>New Near Miss Report Created</h2>";
        } else {
            $body .= "<h2>Near Miss Status Updated</h2>";
            $body .= "<p><strong>Previous Status:</strong> {$oldStatusStr}<br/>";
            $body .= "<strong>New Status:</strong> {$newStatusStr}<br/>";
            // Get user who changed it
            $userName = $_SESSION['user_name'] ?? 'System';
            $body .= "<strong>Action performed by:</strong> {$userName}<br/>";
            $body .= "<strong>Date/Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            $body .= "<hr/>";
        }

        $engineerName = ($details['self_other'] == '1') ? $details['other_engineer_name'] : $details['reported_by'];
        $clusterName = $details['cluster_name'] ?? 'N/A';
        $regionName = $details['region_name'] ?? 'N/A';
        $locAm = $details['location_am'] ?? 'N/A';

        $body .= "<h3>Record Details</h3>";
        $body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 600px;'>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2; width: 35%;'>Near Miss ID</th><td>#{$nearMissId}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Location</th><td>{$locationString}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Region</th><td>{$regionName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Cluster</th><td>{$clusterName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Account Manager</th><td>{$locAm}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Engineer</th><td>{$engineerName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Reporter</th><td>{$details['reported_by']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Near Miss Date</th><td>{$details['near_miss_date']} {$details['near_miss_time']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Priority</th><td>{$details['near_miss_priority']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Incident Summary</th><td>{$details['description_of_occurred']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Current Status</th><td><strong>{$currentStatusText}</strong></td></tr>";
        $body .= "</table>";
        
        $body .= "<br/><p><a href='{$baseUrl}' style='padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 4px;'>View Near Miss Report</a></p>";
        $body .= "</div>";

        helper('email_service');
        $errorMsg = null;
        
        // Pass all recipients to the 'to' parameter as an array
        sendSystemEmail($recipients, $subject, $body, [], [], [], $errorMsg);
        //sendSystemEmail('tikonesmita6@gmail.com', $subject, $body, [], [], [], $errorMsg);
    }
}

if (!function_exists('sendLmraNotification')) {
    /**
     * Triggers LMRA notification email
     *
     * @param int $lmraId
     * @param bool $isNew
     * @param int|null $oldStatus
     * @param int|null $newStatus
     */
    function sendLmraNotification($lmraId, $isNew, $oldStatus = null, $newStatus = null)
    {
        $db = db_connect();
        
        // Fetch LMRA record
        $details = $db->query("
            SELECT alert_lmra_details.*, 
                   IF(alert_lmra_details.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_lmra_details.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_lmra_details.user_id = 0, 'Super Admin', ''))) as reported_by,
                   l.cluster_name, l.region_name, l.account_manager as location_am
            FROM alert_lmra_details 
            LEFT JOIN alert_users ON alert_users.user_id = alert_lmra_details.user_id 
            LEFT JOIN alert_location_master l ON l.location_name = alert_lmra_details.lmra_location
            WHERE lmra_details_id = ?
        ", [$lmraId])->getRowArray();

        if (!$details) {
            log_message('error', "sendLmraNotification: LMRA ID $lmraId not found.");
            return;
        }

        $locationString = $details['lmra_location'] ?? '';
        $recipients = resolveRecipients($locationString);

        if (empty($recipients)) {
            log_message('info', "sendLmraNotification: No recipients found for LMRA ID $lmraId at location '$locationString'.");
            return;
        }

        $statusText = [
            0 => 'Pending',
            1 => 'Verified',
            2 => 'Open',
            3 => 'Cancelled',
            4 => 'Request Required',
            5 => 'Closed'
        ];

        $currentStatusText = $statusText[$details['status']] ?? 'Unknown';
        
        if ($isNew) {
            $subject = "New LMRA Report - {$locationString} - #{$lmraId}";
        } else {
            $oldStatusStr = $statusText[$oldStatus] ?? 'Unknown';
            $newStatusStr = $statusText[$newStatus] ?? 'Unknown';
            $subject = "LMRA Status Updated - {$locationString} - #{$lmraId} - {$oldStatusStr} → {$newStatusStr}";
        }

        $baseUrl = base_url('Reports/Lmra_reports/lmraView/' . $lmraId);
        
        // Build email body
        $body = "<div style='font-family: Arial, sans-serif; color: #333;'>";
        if ($isNew) {
            $body .= "<h2>New LMRA Report Created</h2>";
        } else {
            $body .= "<h2>LMRA Status Updated</h2>";
            $body .= "<p><strong>Previous Status:</strong> {$oldStatusStr}<br/>";
            $body .= "<strong>New Status:</strong> {$newStatusStr}<br/>";
            // Get user who changed it
            $userName = $_SESSION['user_name'] ?? 'System';
            $body .= "<strong>Action performed by:</strong> {$userName}<br/>";
            $body .= "<strong>Date/Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            $body .= "<hr/>";
        }

        $engineerName = ($details['self_other'] == '1') ? $details['other_engineer_name'] : $details['reported_by'];
        $clusterName = $details['cluster_name'] ?? 'N/A';
        $regionName = $details['region_name'] ?? 'N/A';
        $locAm = $details['location_am'] ?? 'N/A';
        
        $body .= "<h3>Record Details</h3>";
        $body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 600px;'>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2; width: 35%;'>LMRA ID</th><td>#{$lmraId}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Location</th><td>{$locationString}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Region</th><td>{$regionName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Cluster</th><td>{$clusterName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Account Manager</th><td>{$locAm}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Engineer</th><td>{$engineerName}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Auditor / Reporter</th><td>{$details['reported_by']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>LMRA Date</th><td>{$details['lmra_visit_date']} {$details['lmra_visit_time']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Task / Assessment</th><td>{$details['task_to_be_perform']}</td></tr>";
        $body .= "<tr><th style='text-align: left; background-color: #f2f2f2;'>Current Status</th><td><strong>{$currentStatusText}</strong></td></tr>";
        $body .= "</table>";
        
        $body .= "<br/><p><a href='{$baseUrl}' style='padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 4px;'>View LMRA Report</a></p>";
        $body .= "</div>";

        helper('email_service');
        $errorMsg = null;
        
        sendSystemEmail($recipients, $subject, $body, [], [], [], $errorMsg);
    }
}
