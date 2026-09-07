-- Migration 006: Add company column to jobs
-- Adds a company name field to jobs so each posting can store its employer.

ALTER TABLE jobs
    ADD COLUMN company VARCHAR(150) DEFAULT NULL AFTER title;
