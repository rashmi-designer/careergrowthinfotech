# Career Grow Infotech Job Portal - Project Rules

## 1. Project Overview

The Career Grow Infotech Job Portal is a PHP + MySQL job portal project designed to connect job seekers with employers in a simple, maintainable, and beginner-friendly way. The goal is to provide a modular structure for both public and authenticated user experiences while keeping the project easy to understand for new developers.

The project uses:
- HTML5
- CSS3
- Bootstrap 5
- Vanilla JavaScript
- PHP
- MySQL
- XAMPP
- Git/GitHub

This project follows a two developer workflow:
- Developer 1 focuses on candidate/public-facing modules and features.
- The lead developer manages architecture, shared files, database structure, admin module, integration, and final review.

The project includes two primary module areas:
- Candidate module: registration, login, profile, resume, job search, job details, applications, and status tracking.
- Admin module: login, dashboard, job management, applicant management, candidate details, settings, and application management.

## 2. Git and Branch Rules

- The main branch is the stable and integration branch.
- Developers must never directly develop on main.
- Every developer works on their own feature or task branch.
- Before starting new work, pull the latest changes from the main branch or the latest approved branch state.
- Push completed work to the developer's own branch.
- Changes must be reviewed before merging into main.
- Never force-push.
- Never delete another developer's branch.
- Do not use git reset --hard unless explicitly approved by the lead developer.
- Commit messages must be meaningful and descriptive.

Recommended commit message format examples:
- feat: add candidate registration
- feat: add job search
- fix: resolve login validation
- ui: improve admin dashboard
- db: add application status field
- docs: update project rules

## 3. Developer Ownership

Each developer should work only on assigned modules or files. Clear ownership must be agreed before development starts.

Lead developer responsibilities:
- Project architecture
- Database structure
- Shared files
- Integration
- Admin module
- Final review
- Merge management

Developer 1 responsibilities:
- Assigned candidate/public modules only
- Must not modify shared/core files without approval

File ownership must be agreed before development starts. If there is any uncertainty about who owns a file, the lead developer must be consulted before making changes.

## 4. Shared Files Rules

These files are treated as shared/core files and must be handled carefully:
- includes/db.php
- includes/header.php
- includes/navbar.php
- includes/footer.php
- includes/auth.php
- includes/admin-auth.php
- includes/functions.php
- database/*.sql

Rules:
- Do not modify shared files unnecessarily.
- If a shared file needs modification, inform the lead developer first.
- Do not create duplicate versions such as db2.php, db-new.php, auth-new.php.
- Reuse existing common components.

## 5. Database Rules

Database name:
- job_portal

Existing tables:
- users
- candidate_profiles
- jobs
- applications
- contact_messages

Rules:
- Never change the database structure casually.
- Never rename or delete columns without approval.
- Never delete project data accidentally.
- Never store plain-text passwords.
- Use password_hash() for passwords.
- Use password_verify() for login.
- Use prepared statements for user input.
- Database changes must be documented in SQL migration files.
- Never commit database passwords or sensitive credentials.
- Do not commit actual uploaded resumes or user data to Git.

## 6. SQL Migration Rules

Existing migrations:
- 001_create_users.sql
- 002_create_candidate_profiles.sql
- 003_create_jobs.sql
- 004_create_applications.sql
- 005_create_contact_messages.sql

Rules:
- Do not edit old migration files after they have been used.
- If a schema change is required, create a new numbered migration.
- Example: 006_add_job_category.sql
- Migration numbers must be sequential.
- Test SQL before sharing it with the other developer.
- Never put fake or sample production data in migration files.

## 7. PHP Rules

- Use PHP 8 compatible code.
- Keep PHP code beginner-friendly and readable.
- Reuse db.php.
- Do not create multiple database connection files.
- Use require_once for shared PHP files where appropriate.
- Validate all user input.
- Escape output using htmlspecialchars() where appropriate.
- Use prepared statements for database queries.
- Do not put passwords directly into source code.
- Do not expose database errors to end users in production UI.
- Keep business logic organized.

## 8. Candidate Module Rules

Candidate functionality includes:
- Registration
- Login
- Profile
- Resume
- Job search
- Job details
- Apply for job
- My applications
- Application status

Rules:
- Candidate can only access their own profile and applications.
- Candidate must be authenticated before applying.
- A candidate cannot apply to the same job more than once.
- Resume upload must be validated.
- Initially support PDF resumes only.
- Do not allow executable files to be uploaded.

## 9. Admin Module Rules

Admin functionality includes:
- Admin login
- Dashboard
- Job management
- Applicant management
- Candidate details
- Application status management
- Settings

Rules:
- Admin pages must require admin authentication.
- Candidate users must not access admin pages.
- Validate all admin inputs.
- Confirm destructive actions such as delete.
- Do not expose sensitive candidate information unnecessarily.

## 10. Frontend/UI Rules

- Use Bootstrap 5.
- Keep the UI responsive.
- Follow Career Grow Infotech branding.
- Maintain consistent spacing, typography and components.
- Reuse navbar, footer and common components.
- Do not create random styles that conflict with existing CSS.
- Use the appropriate CSS file:
  - style.css for global/public styles
  - auth.css for authentication pages
  - candidate.css for candidate pages
  - admin.css for admin pages
- Avoid inline CSS unless absolutely necessary.
- Use meaningful classes and IDs.

## 11. JavaScript Rules

- Use Vanilla JavaScript.
- Keep common JavaScript in main.js.
- Authentication-related JavaScript goes in auth.js.
- Job-related JavaScript goes in jobs.js.
- Candidate-related JavaScript goes in candidate.js.
- Admin-related JavaScript goes in admin.js.
- Do not duplicate the same function across multiple files.
- Validate forms on the client side where useful, but always validate again on the server side.

## 12. Copilot Rules

Before generating code, Copilot must:
- Inspect existing project structure.
- Inspect related files.
- Reuse existing components.
- Avoid modifying unrelated files.

When giving Copilot a task:
- Clearly mention the exact page/module.
- Clearly mention which files can be modified.
- Tell Copilot not to modify unrelated files.
- Do not ask Copilot to rebuild the whole project.
- Do not allow Copilot to invent new database tables or columns without approval.
- Review all Copilot-generated code before committing.

## 13. File Modification Rules

Before editing a file:
- Check whether another developer owns the file.
- Avoid unnecessary changes.
- Do not rename files without agreement.
- Do not delete files without agreement.
- Do not move files without agreement.
- Do not overwrite another developer's work.

## 14. Testing Rules

Every feature must be tested locally using XAMPP.

Test:
- Page loading
- PHP errors
- Database operations
- Form validation
- Authentication
- Authorization
- Responsive layout
- File upload where applicable
- Error handling

A feature is not considered complete until it has been tested.

## 15. Git Workflow

Recommended workflow:

1. Pull latest main changes.
2. Create or use your task branch.
3. Work only on assigned files.
4. Test locally.
5. Check git diff.
6. Check git status.
7. Commit with a meaningful message.
8. Push the branch.
9. Inform the lead developer.
10. Lead reviews the changes.
11. Merge only after review and testing.

Never commit:
- passwords
- API keys
- database credentials
- real candidate resumes
- personal user data
- unnecessary temporary files

## 16. Merge Conflict Rules

If a merge conflict occurs:
- Do not randomly delete code.
- Do not choose "accept theirs" or "accept mine" blindly.
- Stop and inspect the conflict.
- Understand both changes.
- Preserve required functionality from both developers.
- Ask the lead developer when unsure.
- Test the affected functionality after resolving the conflict.

## 17. Communication Rules

Before modifying:
- shared files
- database schema
- authentication
- common UI components

inform the lead developer.

When completing a task, report:
- What was implemented
- Files changed
- Database changes, if any
- Testing performed
- Any known issues

## 18. Final Integration Rules

Before merging any feature:
- Code must be tested locally.
- No unrelated files should be changed.
- No debug code should remain.
- No temporary test files should remain.
- No credentials or sensitive data should be committed.
- Database changes must have a migration file.
- Existing modules must continue working.

## 19. Important Principle

The project must remain:
- Simple
- Maintainable
- Secure
- Consistent
- Modular
- Beginner-friendly

Do not add unnecessary libraries, frameworks, dependencies, tables, files or features without approval.

"Any rule change must be agreed upon by the lead developer before implementation."
