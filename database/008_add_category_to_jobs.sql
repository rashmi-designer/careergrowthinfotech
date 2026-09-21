-- Migration 008: Add category column to jobs
-- Run inside the job_portal database after applying previous migrations.

ALTER TABLE jobs
    ADD COLUMN category VARCHAR(100) DEFAULT NULL AFTER job_type;

-- Note: This migration only adds a nullable `category` column. Existing rows are untouched.
