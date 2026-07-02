# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado no [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.3.0] — 2026-07-02

### Added
- **Dashboard Premium**: animated stat counters, system status grid, Apache/disk/PHP metrics, recent activities timeline, quick actions cards
- **Apache Log Viewer**: severity-based filtering (error/warn/notice/info/debug), textual search with `<mark>` highlighting, live auto-refresh (3s polling), copy individual or all lines, formatted timestamps ("Hoje 14:30", "Ontem 10:15")
- **Severity badges**: lateral stats bar with icons for Exibidos/Erros/Avisos/Notícias/Info

### Changed
- Log message text uses neutral colors (`text-gray-700 dark:text-gray-200`) independent of severity
- All severity badges use consistent neutral gray background (`bg-gray-100 dark:bg-gray-700`) with colored text
- Stat cards layout changed to `flex flex-nowrap flex-1` occupying full horizontal width
- Dark mode contrast improved for notice badges (`dark:bg-gray-700` instead of colored backgrounds)

### Fixed
- Dark mode contrast for "Notícia" badges — now uses same gray background as "Info" with blue text for differentiation
- Log message text readability in dark mode (no longer inherits low-contrast blue text)

---

## [1.2.0] — 2026-07-01

### Added
- **Audit Log** (ActivityLog): full history of create/edit/delete/toggle actions with IP tracking
- **PHP Version Switcher**: per-host PHP version field, FCGID handler in Apache config
- **Batch Operations**: checkbox selection, batch activate/deactivate, batch delete
- **phpMyAdmin Auto-Login**: configurable URL and credentials for one-click phpMyAdmin access
- **Backup & Restore**: full system export as ZIP (database + config), import/restore from ZIP
- **Real-time Log Streaming**: `/logs/stream` endpoint with 3-second polling for live Apache error log updates
- **Quick File Manager**: directory navigation with breadcrumbs, file icons, VSCode open

### Changed
- Complete UI redesign with **Windows 11 Fluent Design** — rounded cards, softer shadows, blur effects, refined typography
- Dark mode refined with `.dark` class hierarchy

---

## [1.1.0] — 2026-06-15

### Added
- SSL certificate regeneration per virtual host
- Export/import virtual hosts as JSON
- `fix-permissions.bat` and `apply-changes.bat` scripts
- Admin authentication with bcrypt password and session-based middleware
- Route throttling on login (5 attempts per minute)
- Server name validation via regex

### Changed
- Service layer refactored to return `['success' => bool, 'output' => string]` for recoverable failures
- Apache restart flow with fallback: `httpd -k restart` → `net stop/start` → PowerShell elevated → `taskkill`

---

## [1.0.0] — 2026-06-01

### Added
- Initial release
- CRUD de Virtual Hosts (create, read, update, delete)
- Automatic Windows hosts file management
- Automatic SSL certificates via mkcert
- Apache configuration generation (port 80 and 443)
- Apache restart with fallback and config validation (`httpd -t`)
- Sync from Apache (import existing vhosts)
- Search with auto-complete
- Dark mode with persistence
- Responsive Brazilian Portuguese interface
- Laravel 13 + PHP 8.3 + SQLite
- Tailwind CSS v4 + Vite 8 + Font Awesome 6

[1.3.0]: https://github.com/viniciuswerneck/virtual-hosts-manager/releases/tag/v1.3.0
[1.2.0]: https://github.com/viniciuswerneck/virtual-hosts-manager/releases/tag/v1.2.0
[1.1.0]: https://github.com/viniciuswerneck/virtual-hosts-manager/releases/tag/v1.1.0
[1.0.0]: https://github.com/viniciuswerneck/virtual-hosts-manager/releases/tag/v1.0.0
