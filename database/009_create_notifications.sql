-- Migration 009: Create candidate notifications table
-- Run this script inside the job_portal database.

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    application_id INT UNSIGNED NULL,
    job_id INT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user_id (user_id),
    KEY idx_notifications_application_id (application_id),
    KEY idx_notifications_job_id (job_id),
    KEY idx_notifications_read_state (user_id, is_read, created_at),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_notifications_application
        FOREIGN KEY (application_id) REFERENCES applications(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_notifications_job
        FOREIGN KEY (job_id) REFERENCES jobs(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
