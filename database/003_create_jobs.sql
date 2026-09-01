-- Migration 003: Create jobs table
-- Run this script inside the job_portal database.

CREATE TABLE IF NOT EXISTS jobs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    skills_required TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    job_type VARCHAR(50) NOT NULL,
    experience_level VARCHAR(50) NOT NULL,
    salary_min DECIMAL(10,2) DEFAULT NULL,
    salary_max DECIMAL(10,2) DEFAULT NULL,
    openings INT UNSIGNED NOT NULL DEFAULT 1,
    last_date DATE DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_jobs_title (title),
    KEY idx_jobs_status_location_last_date (status, location, last_date),
    KEY idx_jobs_job_type_experience (job_type, experience_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
