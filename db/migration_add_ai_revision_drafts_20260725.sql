-- ============================================================
-- Migration : brouillons IA privés
-- Date       : 2026-07-25
-- Contexte   : stocker les cours et quiz IA sans publication globale
-- Rejouable  : OUI (IF NOT EXISTS)
-- ============================================================

CREATE TABLE IF NOT EXISTS `ai_revisions` (
    `Id` INT NOT NULL AUTO_INCREMENT,
    `UserId` INT NOT NULL,
    `Type` VARCHAR(20) NOT NULL,
    `Title` VARCHAR(255) NOT NULL,
    `Subject` VARCHAR(100) NOT NULL,
    `Level` VARCHAR(50) NOT NULL,
    `CourseId` INT DEFAULT NULL,
    `ExerciseIds` TEXT DEFAULT NULL,
    `Metadata` TEXT DEFAULT NULL,
    `Content` LONGTEXT DEFAULT NULL,
    `Status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`Id`),
    KEY `idx_ai_revisions_user` (`UserId`),
    KEY `idx_ai_revisions_type` (`Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `ai_revisions`
    ADD COLUMN IF NOT EXISTS `Content` LONGTEXT DEFAULT NULL AFTER `Metadata`,
    ADD COLUMN IF NOT EXISTS `Status` VARCHAR(20) NOT NULL DEFAULT 'draft' AFTER `Content`,
    ADD COLUMN IF NOT EXISTS `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `CreatedAt`;

-- Validation post-migration.
SHOW COLUMNS FROM `ai_revisions` WHERE `Field` IN ('Content', 'Status', 'UpdatedAt');
SELECT
    COUNT(*) AS `total_revisions`,
    SUM(CASE WHEN `Content` IS NOT NULL THEN 1 ELSE 0 END) AS `private_drafts`
FROM `ai_revisions`;

-- Rollback manuel, uniquement si aucun brouillon privé ne doit être conservé :
-- ALTER TABLE `ai_revisions`
--     DROP COLUMN IF EXISTS `UpdatedAt`,
--     DROP COLUMN IF EXISTS `Status`,
--     DROP COLUMN IF EXISTS `Content`;