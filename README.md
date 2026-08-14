# School Management System

## Overview
This project is an independent final-year full-stack school management system built with PHP and MySQL. It centralizes daily academic and administrative workflows such as user access by role, enrollment, grades, attendance, internal communication, and document/report generation.

## Roles & Responsibilities
The system models multiple user roles through `usuario.tipo` and role-specific pages:
- **Director Geral**: high-level academic/administrative oversight and exports.
- **Director Pedagógico**: pedagogical supervision.
- **Coordenador**: course/turma coordination.
- **Professor**: grade, attendance, and class-related operations.
- **Secretaria**: enrollment, student records, and administrative documents.
- **Aluno**: student dashboard access to personal/academic information.

## Core Features
- Role-based login and dashboard redirection.
- Student enrollment (`matricula`) and academic history workflows.
- Grade (`nota`) and attendance (`frequencia_aluno`) records.
- Course/turma/discipline relationships and assignment flows.
- Internal communications (`comunicado` + recipients).
- Administrative exports and report generation (including PDF/Excel style outputs).
- Profile photo upload and support-material/document upload flows.

## Architecture
Current implementation follows a classic server-rendered PHP architecture:

`Browser (HTML/CSS/JS) → PHP application (pages/includes/process/actions) → MySQL (escoladb)`

- UI is mostly server-rendered pages with some client-side JavaScript.
- Business operations are handled by PHP scripts under `process/` and `actions/`.
- Data persistence is handled in MySQL via `database/escoladb.sql`.

## Database Design
The database script (`database/escoladb.sql`) defines a relational schema with foreign keys. Main entities include:
- `usuario` (base identity + role/type)
- `aluno`, `professor`, `coordenador`, `secretaria`
- `curso`, `turma`, `disciplina`
- `matricula`, `nota`, `frequencia_aluno`
- `comunicado`, `comunicado_destinatario`
- Additional administrative/academic support tables (materials, reports, plans, documents)

## Authentication & Authorization
- Login validates credentials against `usuario` and uses `password_verify` in `includes/common/auth.php`.
- Session data (user id/name/type/email) is stored in `$_SESSION`.
- Authorization is role-based using session checks and permission guards (`includes/common/permissoes.php` and related session checks in process/page flows).

## File Uploads
The project includes upload handlers for profile photos and academic/administrative files.
- Example handlers: `process/upload_foto_perfil.php`, `process/atualizar_foto_perfil.php`.
- Current validations include extension/type and size checks, with files persisted in local upload directories.

## Technical Stack
- **Backend**: PHP (procedural scripts with includes)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap-based UI assets
- **Runtime model**: Server-rendered multi-page application

## Project Structure
```text
/actions              # Action handlers by domain/role
/database             # DB connection and SQL schema/backup
/includes             # Shared components, auth/session/permission helpers
/pages                # Role-based dashboards and feature pages
/process              # Form processing, queries, exports, upload handlers
/public               # Static assets, UI libraries, recovery pages
/config.php           # Paths/base URL and runtime config
```

## Installation
1. Set up a local PHP + MySQL environment (e.g., XAMPP/LAMP).
2. Create a MySQL database named `escoladb`.
3. Import `database/escoladb.sql`.
4. Adjust DB credentials in `database/conexao.php`.
5. Serve the project root through your local web server.
6. Open the login page (`/process/login.php`) and authenticate with seeded or manually created users.

## Security Considerations
This is an educational project and includes some positive practices (prepared statements in many places, password hash verification, session-based access control). However, security is not uniformly implemented across the entire codebase and should not be treated as production-ready.

## Known Limitations
- Not production-hardened.
- Inconsistent data-access style (both `mysqli` and `PDO` patterns appear in the codebase).
- Credentials are configured directly in source (`database/conexao.php`) instead of environment variables.
- Limited automated test coverage in the repository.
- Validation/error handling and access checks are not fully standardized across all modules.

## Future Rebuild
The following is a **future plan only** (not the current implementation):
- React + TypeScript frontend
- Node.js + Express backend API
- PostgreSQL + Prisma data layer
- Zod request/DTO validation
- Dockerized local/dev environments
- Automated testing (unit, integration, end-to-end)

## What I Learned
Building this project end-to-end strengthened my understanding of:
- translating school processes into relational models and role-based workflows,
- implementing authentication/session flows and permission boundaries,
- organizing a full-stack PHP/MySQL project with real CRUD/reporting requirements,
- handling practical trade-offs in maintainability, validation, and security during iterative development.
