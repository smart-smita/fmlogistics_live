-- ====================================================================
-- FM LOGISTICS - SERVER DATABASE DEPLOYMENT SQL MIGRATION
-- Generated for Server Database Deployment
-- Target Database: fmlogistics / unitglo_fmlogistic
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- ====================================================================
-- PART 1: ALTER EXISTING TABLES (NEW COLUMNS)
-- ====================================================================

-- 1.1 Add 'audit_type' column to alert_users (Used for user audit scope: OE, HSE, or Both)
-- MariaDB 10.2+ / MySQL 8.0.29+ supports IF NOT EXISTS.
-- (If your server is an older MySQL version and throws "Duplicate column name", you can safely ignore it)
ALTER TABLE `alert_users` 
  ADD COLUMN IF NOT EXISTS `audit_type` VARCHAR(20) NOT NULL DEFAULT 'Both' COMMENT 'OE, HSE, or Both' AFTER `role_id`;


-- ====================================================================
-- PART 2: NEW TABLES - CENTRALIZED 3 HISTORY TABLES
-- (Tracks Client/Site renames, category changes, cluster/account manager allocations)
-- ====================================================================

-- 2.1 OE History
CREATE TABLE IF NOT EXISTS `alert_oe_history` (
  `history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL,
  `site_id` INT(11) DEFAULT NULL,
  `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | ALLOCATION | DETAILS_UPDATE',
  `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Cluster Manager Changed | Account Manager Changed | etc.',
  `reason` TEXT DEFAULT NULL COMMENT 'User-provided justification/remarks',
  `prev_client_name` VARCHAR(255) NOT NULL,
  `prev_location` VARCHAR(255) DEFAULT NULL,
  `prev_region` VARCHAR(100) DEFAULT NULL,
  `prev_cluster` VARCHAR(100) DEFAULT NULL,
  `prev_account_manager` VARCHAR(100) DEFAULT NULL,
  `prev_email` VARCHAR(255) DEFAULT NULL,
  `prev_status` INT(11) DEFAULT NULL,
  `new_client_name` VARCHAR(255) NOT NULL,
  `new_location` VARCHAR(255) DEFAULT NULL,
  `new_region` VARCHAR(100) DEFAULT NULL,
  `new_cluster` VARCHAR(100) DEFAULT NULL,
  `new_account_manager` VARCHAR(100) DEFAULT NULL,
  `new_email` VARCHAR(255) DEFAULT NULL,
  `new_status` INT(11) DEFAULT NULL,
  `affected_fields` VARCHAR(255) NOT NULL COMMENT 'Comma-separated list of fields changed',
  `affected_audit_count` INT(11) DEFAULT 0,
  `affected_nc_count` INT(11) DEFAULT 0,
  `affected_audit_ids` TEXT DEFAULT NULL,
  `changed_by_user_id` INT(11) DEFAULT NULL,
  `changed_by_user_name` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_oe_client_id` (`client_id`),
  KEY `idx_oe_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 HSE History
CREATE TABLE IF NOT EXISTS `alert_hse_history` (
  `history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL,
  `site_id` INT(11) DEFAULT NULL,
  `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | CATEGORY_CHANGE | ALLOCATION | DETAILS_UPDATE',
  `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Site Category Changed | Cluster Manager Changed | Account Manager Changed | etc.',
  `reason` TEXT DEFAULT NULL COMMENT 'User-provided justification/remarks',
  `prev_client_name` VARCHAR(255) NOT NULL,
  `prev_category` VARCHAR(255) DEFAULT NULL,
  `prev_sub_category` VARCHAR(255) DEFAULT NULL,
  `prev_location` VARCHAR(255) DEFAULT NULL,
  `prev_region` VARCHAR(100) DEFAULT NULL,
  `prev_cluster` VARCHAR(100) DEFAULT NULL,
  `prev_account_manager` VARCHAR(100) DEFAULT NULL,
  `prev_email` VARCHAR(255) DEFAULT NULL,
  `prev_status` INT(11) DEFAULT NULL,
  `new_client_name` VARCHAR(255) NOT NULL,
  `new_category` VARCHAR(255) DEFAULT NULL,
  `new_sub_category` VARCHAR(255) DEFAULT NULL,
  `new_location` VARCHAR(255) DEFAULT NULL,
  `new_region` VARCHAR(100) DEFAULT NULL,
  `new_cluster` VARCHAR(100) DEFAULT NULL,
  `new_account_manager` VARCHAR(100) DEFAULT NULL,
  `new_email` VARCHAR(255) DEFAULT NULL,
  `new_status` INT(11) DEFAULT NULL,
  `affected_fields` VARCHAR(255) NOT NULL,
  `affected_audit_count` INT(11) DEFAULT 0,
  `affected_nc_count` INT(11) DEFAULT 0,
  `affected_audit_ids` TEXT DEFAULT NULL,
  `changed_by_user_id` INT(11) DEFAULT NULL,
  `changed_by_user_name` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_hse_client_id` (`client_id`),
  KEY `idx_hse_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3 Gemba History
CREATE TABLE IF NOT EXISTS `alert_gemba_history` (
  `history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) DEFAULT NULL COMMENT 'HSE Client ID if linked',
  `site_id` INT(11) NOT NULL COMMENT 'alert_gemba_sites.id',
  `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | CATEGORY_CHANGE | ALLOCATION | DETAILS_UPDATE',
  `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Site Category Changed | Cluster Manager Changed | Account Manager Changed | etc.',
  `reason` TEXT DEFAULT NULL COMMENT 'User-provided justification/remarks',
  `prev_site_name` VARCHAR(255) NOT NULL,
  `prev_site_category` VARCHAR(100) DEFAULT NULL,
  `prev_site_type_1` VARCHAR(100) DEFAULT NULL,
  `prev_site_type_2` VARCHAR(100) DEFAULT NULL,
  `prev_region` VARCHAR(100) DEFAULT NULL,
  `prev_cluster` VARCHAR(100) DEFAULT NULL,
  `prev_account_manager` VARCHAR(100) DEFAULT NULL,
  `prev_status` INT(11) DEFAULT NULL,
  `new_site_name` VARCHAR(255) NOT NULL,
  `new_site_category` VARCHAR(100) DEFAULT NULL,
  `new_site_type_1` VARCHAR(100) DEFAULT NULL,
  `new_site_type_2` VARCHAR(100) DEFAULT NULL,
  `new_region` VARCHAR(100) DEFAULT NULL,
  `new_cluster` VARCHAR(100) DEFAULT NULL,
  `new_account_manager` VARCHAR(100) DEFAULT NULL,
  `new_status` INT(11) DEFAULT NULL,
  `affected_fields` VARCHAR(255) NOT NULL,
  `affected_gemba_ids` TEXT DEFAULT NULL,
  `changed_by_user_id` INT(11) DEFAULT NULL,
  `changed_by_user_name` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_gemba_site_id` (`site_id`),
  KEY `idx_gemba_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ====================================================================
-- PART 3: NEW TABLES - EXACT-COLUMN AUDIT BACKUP TABLES
-- (100% column parity for instant disaster recovery and audit data restoration)
-- ====================================================================

-- 3.1 OE Audit Backup (Backs up alert_final_structured_audit)
CREATE TABLE IF NOT EXISTS `alert_oe_audit_backup` (
  `backup_id` INT(11) NOT NULL AUTO_INCREMENT,
  `history_id` INT(11) DEFAULT NULL COMMENT 'Links to OE_history.history_id',
  `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `structured_audit_id` INT(11) NOT NULL,
  `audit_no` TEXT NOT NULL,
  `audit_name` TEXT NOT NULL,
  `reaudit` INT(11) NOT NULL,
  `auditor_name` TEXT NOT NULL,
  `auditee_name` TEXT NOT NULL,
  `region` TEXT NOT NULL,
  `cluster_name` VARCHAR(100) NOT NULL,
  `client_manager_name` VARCHAR(100) NOT NULL,
  `audit_date` DATE NOT NULL,
  `completion_date` DATE NOT NULL,
  `next_date` DATE NOT NULL,
  `report_date` DATE NOT NULL,
  `client_name` TEXT NOT NULL,
  `audit_note` TEXT NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `zone` TEXT NOT NULL,
  `audit_score` FLOAT NOT NULL,
  `audit_revision_no` TEXT NOT NULL,
  `final_remark` TEXT NOT NULL,
  `audit_template_id` INT(11) NOT NULL,
  `audit_by_user_id` INT(11) NOT NULL,
  `action_by_user_id` INT(11) NOT NULL,
  `status` INT(1) NOT NULL,
  `default_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `update_date` DATETIME NOT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_oe_backup_hist` (`history_id`),
  KEY `idx_oe_backup_audit` (`structured_audit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 3.2 HSE Audit Backup (Backs up alert_hse_audit_master)
CREATE TABLE IF NOT EXISTS `alert_hse_audit_backup` (
  `backup_id` INT(11) NOT NULL AUTO_INCREMENT,
  `history_id` INT(11) DEFAULT NULL COMMENT 'Links to HSE_history.history_id',
  `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hse_audit_id` INT(11) NOT NULL,
  `audit_no` TEXT NOT NULL,
  `audit_name` TEXT NOT NULL,
  `audit_template_id` INT(11) NOT NULL,
  `auditor_name` TEXT NOT NULL,
  `auditee_name` TEXT NOT NULL,
  `client_name` TEXT NOT NULL,
  `audit_date` TEXT NOT NULL,
  `template_date` TEXT NOT NULL,
  `next_date` DATE NOT NULL,
  `report_date` DATE NOT NULL,
  `region` TEXT NOT NULL,
  `location` TEXT NOT NULL,
  `score` DECIMAL(10,2) DEFAULT NULL,
  `perform_audit_by` TEXT NOT NULL,
  `main_category` VARCHAR(225) DEFAULT NULL,
  `sub_category` VARCHAR(225) DEFAULT NULL,
  `cluster_name` TEXT NOT NULL,
  `account_manager` TEXT NOT NULL,
  `status` INT(11) NOT NULL,
  `default_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `update_date` DATETIME NOT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_hse_backup_hist` (`history_id`),
  KEY `idx_hse_backup_audit` (`hse_audit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 3.3 Gemba Audit Backup (Backs up alert_gemba_audits)
CREATE TABLE IF NOT EXISTS `alert_gemba_audit_backup` (
  `backup_id` INT(11) NOT NULL AUTO_INCREMENT,
  `history_id` INT(11) DEFAULT NULL COMMENT 'Links to Gemba_history.history_id',
  `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gemba_sr_no` INT(10) UNSIGNED NOT NULL,
  `unique_no` VARCHAR(20) DEFAULT NULL,
  `region` VARCHAR(50) NOT NULL DEFAULT 'South',
  `auditor_name` VARCHAR(100) NOT NULL,
  `audit_category` VARCHAR(100) NOT NULL,
  `audit_type` VARCHAR(100) NOT NULL,
  `audit_doc_type` VARCHAR(100) NOT NULL,
  `site_name` VARCHAR(200) NOT NULL,
  `site_type_1` VARCHAR(50) NOT NULL,
  `site_type_2` VARCHAR(50) NOT NULL,
  `site_category` VARCHAR(50) NOT NULL DEFAULT 'Category-1',
  `audit_mail_received_date` DATE DEFAULT NULL,
  `audit_report_date` DATE NOT NULL,
  `weeknum_raised` TINYINT(3) UNSIGNED DEFAULT NULL,
  `month_raised` DATE DEFAULT NULL,
  `year_month_raised` VARCHAR(10) DEFAULT NULL,
  `year_raised` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `checklist_category` VARCHAR(100) NOT NULL,
  `nc_recommendation` VARCHAR(20) NOT NULL,
  `qhse_remarks` TEXT DEFAULT NULL,
  `observation_point` TEXT NOT NULL,
  `risks_details` TEXT DEFAULT NULL,
  `action_recommendation` TEXT DEFAULT NULL,
  `specifications` TEXT DEFAULT NULL,
  `ua_uc_type` VARCHAR(50) NOT NULL DEFAULT 'UA-UC',
  `risk_severity` VARCHAR(20) NOT NULL,
  `risk_probability` VARCHAR(20) NOT NULL,
  `color_code` VARCHAR(20) NOT NULL,
  `cost_type` VARCHAR(20) NOT NULL,
  `remarks_wm` TEXT DEFAULT NULL,
  `whose_scope` VARCHAR(100) DEFAULT NULL,
  `remarks_corporate` TEXT DEFAULT NULL,
  `ageing_days` INT(10) UNSIGNED DEFAULT NULL,
  `age_bracket` VARCHAR(20) DEFAULT NULL,
  `target_date` DATE DEFAULT NULL,
  `closed_date` DATE DEFAULT NULL,
  `point_status` VARCHAR(50) NOT NULL DEFAULT 'Open',
  `point_category` VARCHAR(50) NOT NULL DEFAULT 'Open Category',
  `closure_status` VARCHAR(50) NOT NULL DEFAULT 'Delayed',
  `weeknum_closed` TINYINT(3) UNSIGNED DEFAULT NULL,
  `month_closed` DATE DEFAULT NULL,
  `year_month_closed` VARCHAR(10) DEFAULT NULL,
  `risk_severity_rating` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  `risk_probability_rating` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  `combined_risk_rating` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1,
  `times_repeated` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  `combined_risk_freq` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1,
  `final_rating` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `followup_by` VARCHAR(100) DEFAULT 'NA',
  `weeknum_yearmonth_raised` VARCHAR(20) DEFAULT NULL,
  `weeknum_yearmonth_closed` VARCHAR(100) DEFAULT NULL,
  `cluster_manager_spoc` VARCHAR(255) DEFAULT NULL,
  `account_manager` VARCHAR(255) DEFAULT NULL,
  `created_by` VARCHAR(255) DEFAULT NULL,
  `updated_by` VARCHAR(255) DEFAULT NULL,
  `status` INT(11) NOT NULL DEFAULT 0,
  `default_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
  `update_date` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `hse_audit_id` INT(11) DEFAULT NULL,
  `hse_detail_id` INT(11) DEFAULT NULL,
  `source_module` VARCHAR(50) DEFAULT 'manual',
  `nc_status` INT(11) NOT NULL DEFAULT 0,
  `nc_remark` TEXT DEFAULT NULL,
  `working_remarks` TEXT DEFAULT NULL,
  `nc_worked_by` TEXT DEFAULT NULL,
  `working_date` DATETIME DEFAULT NULL,
  `nc_after_photo` TEXT DEFAULT NULL,
  `working_uploaded_file` VARCHAR(500) DEFAULT NULL,
  `nc_cluster_reviewed_by` TEXT DEFAULT NULL,
  `nc_cluster_reviewed_date` DATETIME DEFAULT NULL,
  `nc_auditor_reviewed_by` TEXT DEFAULT NULL,
  `nc_auditor_reviewed_date` DATETIME DEFAULT NULL,
  `nc_closed_by` TEXT DEFAULT NULL,
  `nc_closed_date` DATE DEFAULT NULL,
  `closed_remarks` TEXT DEFAULT NULL,
  `closed_uploaded_file` VARCHAR(500) DEFAULT NULL,
  `nc_rejected_by` TEXT DEFAULT NULL,
  `nc_rejected_date` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `action_category` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_gemba_backup_hist` (`history_id`),
  KEY `idx_gemba_backup_audit` (`gemba_sr_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ====================================================================
-- PART 4: NEW TABLES - NC REVIEW TRANSITION & REMINDERS & NOTIFICATIONS
-- (Used by 24h/48h auto-reminders, cron, and navbar notification center)
-- ====================================================================

-- 4.1 NC Status Transitions (OE & GEMBA)
CREATE TABLE IF NOT EXISTS `alert_nc_status_transition` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(20) NOT NULL COMMENT 'OE or GEMBA',
  `nc_id` INT(11) NOT NULL,
  `logical_nc_id` VARCHAR(100) DEFAULT NULL,
  `nc_reference` VARCHAR(100) DEFAULT NULL,
  `review_status` VARCHAR(50) NOT NULL COMMENT 'Cluster Review or Auditor Review',
  `status_code` INT(11) NOT NULL,
  `entered_at` DATETIME NOT NULL,
  `exited_at` DATETIME DEFAULT NULL,
  `is_current` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nc_current` (`module`,`nc_id`,`is_current`),
  KEY `idx_review_timing` (`is_current`,`entered_at`),
  KEY `idx_logical_current` (`logical_nc_id`,`is_current`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4.2 NC Reminder Logs (24h/48h reminder emails)
CREATE TABLE IF NOT EXISTS `alert_nc_reminder_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(20) NOT NULL,
  `nc_id` INT(11) NOT NULL,
  `logical_nc_id` VARCHAR(100) DEFAULT NULL,
  `review_status` VARCHAR(50) NOT NULL,
  `status_entry_time` DATETIME NOT NULL,
  `reminder_sent_at` DATETIME NOT NULL,
  `to_emails` TEXT NOT NULL,
  `cc_emails` TEXT NOT NULL,
  `email_status` VARCHAR(20) NOT NULL COMMENT 'SENT, FAILED, SKIPPED',
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nc_cycle` (`module`,`nc_id`,`review_status`,`status_entry_time`),
  UNIQUE KEY `uk_logical_reminder` (`logical_nc_id`,`review_status`,`status_entry_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4.3 Navbar Real-time In-App Notifications
CREATE TABLE IF NOT EXISTS `alert_navbar_notifications` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `module` VARCHAR(20) NOT NULL,
  `nc_id` INT(11) NOT NULL,
  `logical_nc_id` VARCHAR(100) DEFAULT NULL,
  `nc_reference` VARCHAR(100) NOT NULL,
  `client_site` VARCHAR(255) NOT NULL,
  `review_status` VARCHAR(50) NOT NULL,
  `status_entry_time` DATETIME NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `target_url` VARCHAR(500) NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_cycle_notif` (`user_id`,`module`,`nc_id`,`review_status`,`status_entry_time`),
  UNIQUE KEY `uk_notif_logical_cycle` (`user_id`,`logical_nc_id`,`review_status`,`status_entry_time`),
  KEY `idx_user_unread` (`user_id`,`is_read`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ====================================================================
-- PART 5: NEW TABLES - USER & APPLICATION FEEDBACK
-- (Used by feedback modals and admin feedback views)
-- ====================================================================

-- 5.1 General User Feedback
CREATE TABLE IF NOT EXISTS `alert_user_feedback` (
  `feedback_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `rating` TINYINT(1) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `comments` TEXT NOT NULL,
  `suggestion` TEXT DEFAULT NULL,
  `submitted_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `idx_feedback_category` (`category`),
  KEY `idx_feedback_rating` (`rating`),
  KEY `idx_feedback_date` (`submitted_at`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5.2 Application Status Feedback (12 Survey Questions)
CREATE TABLE IF NOT EXISTS `alert_app_status_feedback` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `q1` ENUM('Yes','No','NA') NOT NULL,
  `q2` ENUM('Yes','No','NA') NOT NULL,
  `q3` ENUM('Yes','No','NA') NOT NULL,
  `q4` ENUM('Yes','No','NA') NOT NULL,
  `q5` ENUM('Yes','No','NA') NOT NULL,
  `q6` ENUM('Yes','No','NA') NOT NULL,
  `q7` ENUM('Yes','No','NA') NOT NULL,
  `q8` ENUM('Yes','No','NA') NOT NULL,
  `q9` ENUM('Yes','No','NA') NOT NULL,
  `q10` ENUM('Yes','No','NA') NOT NULL,
  `q11` ENUM('Yes','No','NA') NOT NULL,
  `q12` ENUM('Yes','No','NA') NOT NULL,
  `comments` TEXT DEFAULT NULL,
  `submitted_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_status_user` (`user_id`),
  KEY `idx_app_status_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ====================================================================
-- PART 6: OPTIONAL CLEANUP OF OBSOLETE / REDUNDANT TEMPORARY TABLES
-- ====================================================================
DROP TABLE IF EXISTS `alert_oe_site_rename_audit_backup`;
DROP TABLE IF EXISTS `alert_hse_site_rename_audit_backup`;
DROP TABLE IF EXISTS `alert_gemba_site_rename_audit_backup`;
DROP TABLE IF EXISTS `alert_oe_site_rename_history`;
DROP TABLE IF EXISTS `alert_hse_site_rename_history`;
DROP TABLE IF EXISTS `alert_site_allocation_history`;
DROP TABLE IF EXISTS `alert_gemba_allocation_history`;
DROP TABLE IF EXISTS `alert_oe_audit_history`;
DROP TABLE IF EXISTS `alert_hse_audit_history`;
DROP TABLE IF EXISTS `alert_client_rename_logs`;
DROP TABLE IF EXISTS `alert_client_dependency_sync_logs`;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
