-- SQL script to create the new table for the UNEB assessment module

CREATE TABLE `uneb_assessments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_id` BIGINT(20) UNSIGNED NOT NULL,
  `class_level_id` BIGINT(20) UNSIGNED NOT NULL,
  `assessment_type` ENUM('guideline', 'sample_question') NOT NULL,
  `content` TEXT NOT NULL,
  `source_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assessments_subject_id_foreign` (`subject_id`),
  KEY `assessments_class_level_id_foreign` (`class_level_id`),
  CONSTRAINT `assessments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assessments_class_level_id_foreign` FOREIGN KEY (`class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
