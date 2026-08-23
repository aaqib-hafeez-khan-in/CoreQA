# CoreQA

CoreQA is a lightweight Q&A forum built with native PHP, PDO, SQLite/MySQL, and a small MVC-style application core.

## Overview

CoreQA provides a focused platform for asking questions, sharing answers, organizing discussions, and moderating community content.

- User registration and authentication
- Questions, replies, tags, search, and pagination
- Markdown content rendering
- Voting and reputation tracking
- Moderator and administrator workflows
- CSRF protection and request throttling
- SQLite for local development
- MySQL or MariaDB for production

The application is intentionally dependency-light. The `public/` directory is the web root; application code, configuration, database schema, and runtime state remain outside the public document root.

## Requirements

- PHP 8.2+
- PDO with the required database driver
- Apache 2.4+ with `mod_rewrite`, or Nginx configured to route requests to `public/index.php`
- SQLite for local development, or MySQL 8+/MariaDB for production

## Project structure

```text
CoreQA/
├── app/
│   ├── Controllers/    Request handling and application flows
│   ├── Core/           Router, container, views, and shared helpers
│   ├── Models/         Database-backed domain models
│   ├── Services/       Authentication, CSRF, caching, Markdown, and rate limiting
│   └── Views/          PHP templates
├── api/                API entry point
├── bin/                Database schema
├── config/             Application configuration and routes
├── public/             Web root and static assets
├── storage/            Runtime state and logs
└── README.md
```

## Local development

Clone the repository and create a local environment file:

```bash
git clone https://github.com/aaqib-hafeez-khan-in/CoreQA.git
cd CoreQA
cp .env.example .env
```

For a simple local setup, use SQLite and start PHP's development server:

```bash
php -S localhost:8080 -t public
```

Then open `http://localhost:8080/`.

The built-in PHP server is for development only. Use a production web server for public deployments.

## Configuration

Runtime configuration is supplied through environment variables. Use `.env.example` as the starting point and never commit real credentials.

Example local configuration:

```text
APP_ENV=local
APP_BASE_URL=/
SESSION_NAME=coreqa_sid
CSRF_KEY=replace-with-a-random-secret
DB_DSN=sqlite:/absolute/path/to/storage/database.sqlite
DB_USER=
DB_PASS=
```

For production, set `APP_ENV=production` and generate a strong random `CSRF_KEY`.

### MySQL or MariaDB

```text
DB_DSN=mysql:host=127.0.0.1;dbname=coreqa;charset=utf8mb4
DB_USER=coreqa
DB_PASS=<secret>
```

Use a dedicated database account with only the permissions required by CoreQA.

## Database setup

For MySQL or MariaDB, create the database and apply the supplied schema:

```bash
mysql -u coreqa -p coreqa < bin/schema.sql
```

For local SQLite development, the application can initialize the required schema when the database does not already exist.

## Web server

The web server must use `public/` as its document root rather than the repository root. This prevents application source, configuration, and environment files from being directly exposed.

For Apache, enable `mod_rewrite` and allow the rewrite configuration in `public/`.

For Nginx, serve static files from `public/` and route application requests to `public/index.php` through PHP-FPM.

## Security

CoreQA includes a baseline production security model:

- Passwords use PHP's password hashing API.
- Authentication regenerates the session ID after login.
- Session cookies use `HttpOnly` and `SameSite=Lax`.
- HTTPS deployments can enable secure cookies and HSTS.
- State-changing requests use CSRF protection.
- User-facing HTML is escaped through the shared output helper.
- Database access uses PDO prepared statements.
- Login attempts are rate limited.
- Production deployments do not seed demo accounts.
- Environment files and runtime state are excluded from source control.

Production deployments should additionally use HTTPS, a dedicated database account, regular database backups, restricted filesystem permissions, and current supported PHP/security updates.

## Development checks

CoreQA does not use GitHub Actions or another repository-hosted CI pipeline. Validation is intentionally performed locally before deployment.

Check PHP syntax with:

```bash
find . -type f -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Run locally with:

```bash
php -S localhost:8080 -t public
```

## Deployment

CoreQA requires a PHP runtime and persistent database, so it should be deployed to PHP-capable hosting rather than static hosting.

Recommended production architecture:

```text
Internet
   |
 HTTPS
   |
 Nginx or Apache
   |
 PHP-FPM
   |
 CoreQA
   |
 MySQL/MariaDB
```

GitHub Pages is not suitable because it cannot execute PHP. Netlify is also not the recommended deployment target for this server-side PHP application.

A small Linux VPS or managed PHP host is a better fit. For a VPS deployment, configure PHP-FPM, Nginx or Apache, MySQL/MariaDB, HTTPS, backups, and `public/` as the document root.

### Production checklist

1. Use PHP 8.2 or newer.
2. Set `APP_ENV=production`.
3. Generate a strong `CSRF_KEY`.
4. Configure a production MySQL/MariaDB database and dedicated user.
5. Apply `bin/schema.sql`.
6. Point the web server at `public/`.
7. Enable HTTPS.
8. Keep `.env` and runtime storage out of source control.
9. Configure database backups and appropriate filesystem permissions.
10. Run the local PHP syntax check before deployment.

## Contact

**Author:** Aaqibhafeez Khan  
**GitHub:** [@aaqib-hafeez-khan-in](https://github.com/aaqib-hafeez-khan-in)

## License

No license is currently declared. Add an explicit license before distributing CoreQA as an open-source project.
