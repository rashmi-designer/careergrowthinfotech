-- Migration 006: Add is_read field to contact_messages table
-- Run this script inside the job_portal database.

ALTER TABLE contact_messages
ADD COLUMN is_read TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER created_at,
ADD KEY idx_contact_messages_is_read (is_read);

-- Set all existing messages as unread by default
UPDATE contact_messages SET is_read = 0 WHERE is_read IS NULL;
 