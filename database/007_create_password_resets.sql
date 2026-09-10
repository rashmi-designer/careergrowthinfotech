-- Migration: 007_create_password_resets.sql
-- Purpose: Create password_resets table to store hashed reset tokens
-- Supports both candidate and admin users from `users` table

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_password_resets_user_id` (`user_id`),
  INDEX `idx_password_resets_token_hash` (`token_hash`),
  INDEX `idx_password_resets_expires_at` (`expires_at`),
  CONSTRAINT `fk_password_resets_user_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes:
-- - Only token hashes are stored (no plaintext tokens).
-- - `expires_at` is provided by application code (e.g., 1 hour from creation).
-- - `used_at` is NULL until the token has been consumed; set to current time when used.
-- - Multiple resets per user allowed; application logic should decide which tokens to accept.
