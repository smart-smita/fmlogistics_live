-- ============================================================
-- FM LOGISTICS ALERT - TABLE NAMING CONVENTION MIGRATION
-- Version: 1.0
-- Date: 2026-10-15
-- Target Database: fmlogistics_live / unitglo_fmlogistic
-- Target MariaDB/MySQL: MariaDB 10.4+ / MySQL 8.0+
-- ============================================================
--
-- OBJECTIVE:
--   Rename every non-compliant application table to follow the
--   mandatory `alert_` prefix + lowercase naming convention.
--
-- TABLES RENAMED (13):
--   alter_audit_category_master   => alert_audit_category_master
--   email_settings                => alert_email_settings
--   setting                       => alert_setting
--   software_definitions          => alert_software_definitions
--   software_documents            => alert_software_documents
--   software_document_downloads   => alert_software_document_downloads
--   software_document_versions    => alert_software_document_versions
--   OE_history                    => alert_oe_history
--   HSE_history                   => alert_hse_history
--   Gemba_history                 => alert_gemba_history
--   OE_audit_backup               => alert_oe_audit_backup
--   HSE_audit_backup              => alert_hse_audit_backup
--   Gemba_audit_backup            => alert_gemba_audit_backup
--
-- EXCEPTIONS:
--   ci_sessions  - CodeIgniter session table. Session driver is
--                  configured as FileHandler (not DatabaseHandler),
--                  so this table is unused by the application.
--
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

-- ============================================================
-- SECTION A: PRE-MIGRATION CHECKS
-- Run these SELECT queries FIRST. For each row:
--   old_exists=1 means the source table is present (good)
--   new_exists_conflict=1 means the target name is taken (STOP!)
-- Only proceed with RENAME if old_exists=1 AND new_exists_conflict=0
-- ============================================================

SELECT
    'alter_audit_category_master' AS old_table,
    'alert_audit_category_master' AS new_table,
    (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alter_audit_category_master') AS old_exists,
    (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alert_audit_category_master') AS new_exists_conflict
UNION ALL SELECT 'email_settings', 'alert_email_settings',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='email_settings'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_email_settings')
UNION ALL SELECT 'setting', 'alert_setting',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='setting'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_setting')
UNION ALL SELECT 'software_definitions', 'alert_software_definitions',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='software_definitions'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_software_definitions')
UNION ALL SELECT 'software_documents', 'alert_software_documents',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='software_documents'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_software_documents')
UNION ALL SELECT 'software_document_downloads', 'alert_software_document_downloads',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='software_document_downloads'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_software_document_downloads')
UNION ALL SELECT 'software_document_versions', 'alert_software_document_versions',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='software_document_versions'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_software_document_versions')
UNION ALL SELECT 'OE_history', 'alert_oe_history',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='OE_history'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_oe_history')
UNION ALL SELECT 'HSE_history', 'alert_hse_history',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='HSE_history'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_hse_history')
UNION ALL SELECT 'Gemba_history', 'alert_gemba_history',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Gemba_history'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_gemba_history')
UNION ALL SELECT 'OE_audit_backup', 'alert_oe_audit_backup',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='OE_audit_backup'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_oe_audit_backup')
UNION ALL SELECT 'HSE_audit_backup', 'alert_hse_audit_backup',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='HSE_audit_backup'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_hse_audit_backup')
UNION ALL SELECT 'Gemba_audit_backup', 'alert_gemba_audit_backup',
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Gemba_audit_backup'),
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alert_gemba_audit_backup');


-- ============================================================
-- SECTION B: TABLE RENAMING
-- RENAME TABLE is atomic and preserves all data, indexes,
-- primary keys, foreign keys, and AUTO_INCREMENT values.
-- Each statement must succeed before continuing to the next.
-- ============================================================

-- B.1  alter_audit_category_master => alert_audit_category_master
--      (typo fix: 'alter' was a misspelling of 'alert')
RENAME TABLE `alter_audit_category_master` TO `alert_audit_category_master`;

-- B.2  email_settings => alert_email_settings
RENAME TABLE `email_settings` TO `alert_email_settings`;

-- B.3  setting => alert_setting
RENAME TABLE `setting` TO `alert_setting`;

-- B.4  software_definitions => alert_software_definitions
RENAME TABLE `software_definitions` TO `alert_software_definitions`;

-- B.5  software_documents => alert_software_documents
RENAME TABLE `software_documents` TO `alert_software_documents`;

-- B.6  software_document_downloads => alert_software_document_downloads
RENAME TABLE `software_document_downloads` TO `alert_software_document_downloads`;

-- B.7  software_document_versions => alert_software_document_versions
RENAME TABLE `software_document_versions` TO `alert_software_document_versions`;

-- B.8-B.13: History and backup tables
-- NOTE: These tables may or may not exist depending on whether
-- server_database_update_deploy.sql was run. Skip any RENAME
-- for a table that does not exist in your database.

-- B.8  OE_history => alert_oe_history
RENAME TABLE `OE_history` TO `alert_oe_history`;

-- B.9  HSE_history => alert_hse_history
RENAME TABLE `HSE_history` TO `alert_hse_history`;

-- B.10  Gemba_history => alert_gemba_history
RENAME TABLE `Gemba_history` TO `alert_gemba_history`;

-- B.11  OE_audit_backup => alert_oe_audit_backup
RENAME TABLE `OE_audit_backup` TO `alert_oe_audit_backup`;

-- B.12  HSE_audit_backup => alert_hse_audit_backup
RENAME TABLE `HSE_audit_backup` TO `alert_hse_audit_backup`;

-- B.13  Gemba_audit_backup => alert_gemba_audit_backup
RENAME TABLE `Gemba_audit_backup` TO `alert_gemba_audit_backup`;


-- ============================================================
-- SECTION C: ALTER TABLE
-- No additional ALTER TABLE statements are required.
-- RENAME TABLE automatically preserves all indexes, constraints,
-- foreign keys, triggers, and AUTO_INCREMENT settings.
-- ============================================================


-- ============================================================
-- SECTION D: VIEWS, TRIGGERS, STORED PROCEDURES
-- No views, triggers, or stored procedures referencing the
-- renamed tables were found in the SQL dump. No action needed.
-- ============================================================


-- ============================================================
-- SECTION E: POST-MIGRATION VERIFICATION
-- ============================================================

-- E.1  Non-compliant tables (Expected: 0 rows)
SELECT TABLE_NAME AS non_compliant_table
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME NOT LIKE 'alert_%'
  AND TABLE_NAME NOT IN ('ci_sessions')
ORDER BY TABLE_NAME;

-- E.2  Confirm 13 renamed tables exist with new names
SELECT TABLE_NAME, TABLE_ROWS, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'alert_audit_category_master',
    'alert_email_settings',
    'alert_setting',
    'alert_software_definitions',
    'alert_software_documents',
    'alert_software_document_downloads',
    'alert_software_document_versions',
    'alert_oe_history',
    'alert_hse_history',
    'alert_gemba_history',
    'alert_oe_audit_backup',
    'alert_hse_audit_backup',
    'alert_gemba_audit_backup'
  )
ORDER BY TABLE_NAME;

-- E.3  Confirm old names no longer exist (Expected: 0 rows)
SELECT TABLE_NAME AS still_old_name
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'alter_audit_category_master',
    'email_settings',
    'setting',
    'software_definitions',
    'software_documents',
    'software_document_downloads',
    'software_document_versions',
    'OE_history', 'HSE_history', 'Gemba_history',
    'OE_audit_backup', 'HSE_audit_backup', 'Gemba_audit_backup'
  );

-- E.4  Count of alert_ tables (should be >= 70)
SELECT COUNT(*) AS total_alert_tables
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'alert_%';

-- E.5  Full compliant table inventory
SELECT TABLE_NAME, TABLE_ROWS, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;


-- ============================================================
-- ROLLBACK SCRIPT
-- (Run this block ONLY if the migration needs to be reversed)
-- ============================================================

/*
-- ROLLBACK: Reverse all 13 renames

SET FOREIGN_KEY_CHECKS = 0;

RENAME TABLE `alert_audit_category_master`        TO `alter_audit_category_master`;
RENAME TABLE `alert_email_settings`               TO `email_settings`;
RENAME TABLE `alert_setting`                      TO `setting`;
RENAME TABLE `alert_software_definitions`         TO `software_definitions`;
RENAME TABLE `alert_software_documents`           TO `software_documents`;
RENAME TABLE `alert_software_document_downloads`  TO `software_document_downloads`;
RENAME TABLE `alert_software_document_versions`   TO `software_document_versions`;
RENAME TABLE `alert_oe_history`                   TO `OE_history`;
RENAME TABLE `alert_hse_history`                  TO `HSE_history`;
RENAME TABLE `alert_gemba_history`                TO `Gemba_history`;
RENAME TABLE `alert_oe_audit_backup`              TO `OE_audit_backup`;
RENAME TABLE `alert_hse_audit_backup`             TO `HSE_audit_backup`;
RENAME TABLE `alert_gemba_audit_backup`           TO `Gemba_audit_backup`;

SET FOREIGN_KEY_CHECKS = 1;

-- Also revert all PHP model/migration/service code changes.
*/

SET FOREIGN_KEY_CHECKS = 1;
