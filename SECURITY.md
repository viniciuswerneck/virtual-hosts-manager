# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in Hosts Manager, please report it privately by opening a **Security Advisory** on GitHub:

1. Go to https://github.com/viniciuswerneck/virtual-hosts-manager/security/advisories
2. Click **New draft security advisory**
3. Provide a detailed description, steps to reproduce, and potential impact

We will respond within **48 hours** with an acknowledgement and timeline for a fix.

**Do not** report security issues via public Issues or Discussions.

## Scope

The following areas are in scope for security reports:

- Authentication bypass or privilege escalation
- SQL injection
- Cross-site scripting (XSS) in rendered views
- Path traversal in file manager or backup features
- Remote code execution via Apache config manipulation
- Session fixation or CSRF on authenticated actions

## Supported Versions

| Version | Supported |
|---|---|
| 1.x (latest) | ✅ |
| Older releases | ❌ |

## Best Practices for Self-Hosting

1. **Always use HTTPS** in production (reverse proxy with SSL)
2. **Change the default password** immediately after install
3. **Keep `.env` restricted** — never commit it to version control
4. **Set `ADMIN_PASSWORD` to empty** only on isolated localhost environments
5. **Run `php artisan config:cache`** in production for performance
6. **Regularly update dependencies**: `composer update` and `npm update`

## Encryption

- Passwords are stored as **bcrypt** hashes (PHP's `password_hash`)
- No user model — authentication is session-based with a single admin password
- SQLite database should be protected by filesystem permissions
