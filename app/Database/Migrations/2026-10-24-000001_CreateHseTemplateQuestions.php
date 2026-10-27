<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHseTemplateQuestions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Create alert_hse_template_questions if not exists
        $createTableSql = "CREATE TABLE IF NOT EXISTS `alert_hse_template_questions` (
            `template_question_id` INT(11) NOT NULL AUTO_INCREMENT,
            `audit_template_id` INT(11) NOT NULL,
            `question_order` INT(11) NOT NULL DEFAULT 0,
            `category_id` VARCHAR(50) NOT NULL,
            `category_name` VARCHAR(255) NOT NULL,
            `question_text` TEXT NOT NULL,
            `default_value` VARCHAR(20) DEFAULT 'NA',
            `findings` TEXT DEFAULT NULL,
            `risk` TEXT DEFAULT NULL,
            `actions_recommendation` TEXT DEFAULT NULL,
            `action_category` VARCHAR(150) DEFAULT NULL,
            `ua_uc` VARCHAR(100) DEFAULT NULL,
            `risk_severity` VARCHAR(50) DEFAULT NULL,
            `risk_probability` VARCHAR(50) DEFAULT NULL,
            `color_code` VARCHAR(50) DEFAULT NULL,
            `cost_type` VARCHAR(50) DEFAULT NULL,
            `combined_risk_rating` VARCHAR(50) DEFAULT NULL,
            `note` TEXT DEFAULT NULL,
            `capa_json` LONGTEXT DEFAULT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`template_question_id`),
            INDEX `idx_template_id` (`audit_template_id`),
            INDEX `idx_category_id` (`category_id`),
            INDEX `idx_order` (`question_order`),
            UNIQUE KEY `uq_template_cat` (`audit_template_id`, `category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($createTableSql);

        // 2. Add columns to alert_audit_questions if not already present
        $existingCols = [];
        $colQuery = $db->query("SHOW COLUMNS FROM `alert_audit_questions`");
        foreach ($colQuery->getResultArray() as $col) {
            $existingCols[] = $col['Field'];
        }

        $colsToAdd = [
            'template_question_id' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `template_question_id` INT(11) NULL DEFAULT NULL AFTER `group_id`",
            'site_name' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `site_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `audit_template_id`",
            'question_order' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `question_order` INT(11) NOT NULL DEFAULT 0 AFTER `template_question_id`",
            'findings' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `findings` TEXT DEFAULT NULL AFTER `audit_question_default_value`",
            'risk' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `risk` TEXT DEFAULT NULL AFTER `findings`",
            'actions_recommendation' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `actions_recommendation` TEXT DEFAULT NULL AFTER `risk`",
            'action_category' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `action_category` VARCHAR(150) DEFAULT NULL AFTER `actions_recommendation`",
            'ua_uc' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `ua_uc` VARCHAR(100) DEFAULT NULL AFTER `action_category`",
            'risk_severity' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `risk_severity` VARCHAR(50) DEFAULT NULL AFTER `ua_uc`",
            'risk_probability' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `risk_probability` VARCHAR(50) DEFAULT NULL AFTER `risk_severity`",
            'color_code' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `color_code` VARCHAR(50) DEFAULT NULL AFTER `risk_probability`",
            'cost_type' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `cost_type` VARCHAR(50) DEFAULT NULL AFTER `color_code`",
            'combined_risk_rating' => "ALTER TABLE `alert_audit_questions` ADD COLUMN `combined_risk_rating` VARCHAR(50) DEFAULT NULL AFTER `cost_type`"
        ];

        foreach ($colsToAdd as $colName => $sql) {
            if (!in_array($colName, $existingCols)) {
                $db->query($sql);
            }
        }

        // Add index on template_question_id if not present
        $idxQuery = $db->query("SHOW INDEX FROM `alert_audit_questions` WHERE Key_name = 'idx_template_question_id'");
        if ($idxQuery->getNumRows() == 0) {
            $db->query("ALTER TABLE `alert_audit_questions` ADD INDEX `idx_template_question_id` (`template_question_id`)");
        }

        // 3. Seed canonical 290 questions for existing Template ID 3 if template 3 exists
        $tpl3 = $db->table('alert_audit_template')->where('audit_template_id', 3)->get()->getRowArray();
        if ($tpl3) {
            $existingTpl3Count = $db->table('alert_hse_template_questions')->where('audit_template_id', 3)->countAllResults();
            if ($existingTpl3Count == 0) {
                // Populate from reference CSV
                $csvPath = '/Users/Unitglo-Suraj/Downloads/HSE_Audit_question_sample_excel.csv';
                if (file_exists($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
                    fgetcsv($handle); // header
                    $seenCats = [];
                    $order = 1;
                    $insertBatch = [];

                    while (($row = fgetcsv($handle)) !== false) {
                        $catId = trim($row[2] ?? '');
                        if (empty($catId) || isset($seenCats[$catId])) {
                            continue;
                        }
                        $seenCats[$catId] = true;

                        $capa = [
                            "findings" => ["text" => "Findings", "yes" => "", "no" => $row[6] ?: "", "na" => ""],
                            "risk" => ["text" => "Risk", "yes" => "", "no" => $row[7] ?: "", "na" => ""],
                            "actions" => ["text" => "Actions/Recommendation", "yes" => "", "no" => $row[8] ?: "", "na" => ""],
                            "action_category" => ["text" => "Action Category", "yes" => "", "no" => $row[9] ?: "", "na" => ""],
                            "ua_uc" => ["text" => "UA-UC", "yes" => "", "no" => $row[10] ?: "", "na" => ""],
                            "risk_severity" => ["text" => "Risk Severity", "yes" => "", "no" => $row[11] ?: "", "na" => ""],
                            "risk_probability" => ["text" => "Risk Probability", "yes" => "", "no" => $row[12] ?: "", "na" => ""],
                            "color_code" => ["text" => "Color Code", "yes" => "", "no" => $row[13] ?: "", "na" => ""],
                            "cost_type" => ["text" => "Cost Type", "yes" => "", "no" => $row[14] ?: "", "na" => ""],
                            "combined_risk_rating" => ["text" => "Combined Risk Rating", "yes" => "", "no" => $row[15] ?: "", "na" => ""]
                        ];

                        $insertBatch[] = [
                            'audit_template_id' => 3,
                            'question_order' => $order++,
                            'category_id' => $catId,
                            'category_name' => trim($row[3] ?? ''),
                            'question_text' => trim($row[4] ?? ''),
                            'default_value' => trim($row[5] ?? '') ?: 'NA',
                            'findings' => trim($row[6] ?? ''),
                            'risk' => trim($row[7] ?? ''),
                            'actions_recommendation' => trim($row[8] ?? ''),
                            'action_category' => trim($row[9] ?? ''),
                            'ua_uc' => trim($row[10] ?? ''),
                            'risk_severity' => trim($row[11] ?? ''),
                            'risk_probability' => trim($row[12] ?? ''),
                            'color_code' => trim($row[13] ?? ''),
                            'cost_type' => trim($row[14] ?? ''),
                            'combined_risk_rating' => trim($row[15] ?? ''),
                            'note' => trim($row[16] ?? ''),
                            'capa_json' => json_encode($capa),
                            'status' => 1
                        ];
                    }
                    fclose($handle);

                    if (!empty($insertBatch)) {
                        $db->table('alert_hse_template_questions')->insertBatch($insertBatch);
                    }
                }

                // Link template_question_id in alert_audit_questions for template 3
                $linkSql = "UPDATE `alert_audit_questions` a
                    JOIN `alert_hse_template_questions` t 
                      ON t.`audit_template_id` = a.`audit_template_id` 
                     AND t.`category_id` = a.`audit_category_id`
                    SET a.`template_question_id` = t.`template_question_id`,
                        a.`question_order` = t.`question_order`
                    WHERE a.`audit_template_id` = 3 AND a.`template_question_id` IS NULL";
                $db->query($linkSql);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query("DROP TABLE IF EXISTS `alert_hse_template_questions`");
    }
}
