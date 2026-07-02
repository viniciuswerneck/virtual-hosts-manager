# AGENTS.md

## Personas

### Backend / DevOps
You are an Engenheiro de Software Sênior, DevOps PHP, Administrador Linux, DBA MySQL e Arquiteto Laravel with 20+ years of experience. Tech Lead level. You never answer superficially — always investigate deeply before proposing solutions. Always consider performance, security, and scalability.

### Front-end / UI Design
You are a UI Designer, UX Designer, Product Designer, Front-end Architect and Design Systems Specialist with 20+ years of experience building modern interfaces for corporate web apps and SaaS. You think like designers from Apple, Stripe, Linear, Notion, Vercel, Figma, Tailwind UI. Your goal is to produce commercial-grade interfaces that feel premium, minimal, elegant, accessible, responsive, and consistent. Follow WCAG AA/AAA, use proper hierarchy, spacing (4px/8px scale), dark/light modes, and never produce generic or outdated UIs. Icons: prefer Font Awesome 6 or Heroicons. Animations: subtle, purposeful. No heavy shadows, saturated colors, or cluttered layouts.

## Commands

### Dev server / watch
```bash
composer run dev
# Runs: php artisan serve + queue:listen + pail + vite --kill-others
```

### Run all tests
```bash
composer run test
# Runs: php artisan config:clear && php artisan test
```

### Run a single test file
```bash
php artisan test tests/Unit/ApacheServiceTest.php
php artisan test tests/Feature/SettingsControllerTest.php
```

### Run a specific test method
```bash
php artisan test --filter test_parse_existing_returns_empty_when_file_missing
```

### Run PHPUnit directly
```bash
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Unit/ApacheServiceTest.php
./vendor/bin/phpunit --filter test_index_returns_all_config_keys
```

### Lint / code style (Laravel Pint)
```bash
./vendor/bin/pint        # dry run (shows diffs)
./vendor/bin/pint --test  # check mode (exit code)
```

### Static analysis
```bash
# Not configured. Larastan would go here if added.
```

### Config cache
```bash
php artisan config:clear
php artisan config:cache   # production only
```

### Database
```bash
php artisan migrate
php artisan migrate --force
php artisan db:seed
```

### Queue
```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Production build
```bash
npm run build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Code Style

### Imports
- One `use` statement per line, grouped: framework, then app, then PHP native.
- Sort by length, then alphabetically. No blank lines between groups of the same namespace.
- FQN inline only in routes/closure contexts (e.g. `\Illuminate\Http\Request`).

### Formatting
- **PSR-12** with Laravel Pint enforcement (`./vendor/bin/pint`).
- Opening braces on same line for classes/methods, on same line for control structures.
- Single blank line between methods, two blank lines between top-level statements.
- No trailing whitespace. Line endings LF.

### Types / DocBlocks
- PHP 8.3 typed properties everywhere. No `@var` docblocks for typed properties.
- DocBlocks on interface methods, custom exceptions, and complex array returns only.
- `@param` and `@return` for non-trivial array shapes. Omit when types are clear from signature.
- Use `void` return type explicitly. Use `\Throwable` (not `\Exception`) for broad catches.
- Prefer constructor promotion: `public function __construct(private SomeService $dep) {}`

### Naming Conventions
- **Classes**: PascalCase (e.g. `ApacheLogController`, `HostsFileService`).
- **Methods**: camelCase (e.g. `parseExisting`, `applyApacheConfig`).
- **Variables/properties**: camelCase (e.g. `$serverName`, `$certDir`).
- **Database columns**: snake_case (e.g. `server_name`, `document_root`, `ssl_enabled`).
- **Routes**: kebab-case (e.g. `virtual-hosts.index`, `logs.index`).
- **Blade files**: kebab-case (e.g. `virtual-hosts.index.blade.php`).
- **Config keys**: snake_case (e.g. `virtualhosts.apache_error_log`).

### Error Handling
- Catch `\Throwable` (not `\Exception`) for broad error handling — avoids missing PHP engine errors.
- Wrap external I/O (file writes, exec, shell) in try/catch.
- Throw `\RuntimeException` with user-facing messages in Portuguese.
- Service methods return `['success' => bool, 'output' => string]` arrays for recoverable failures.
- Controllers catch and redirect with `->with('error', $msg)`.
- Silently catch rollback operations in destructor-like scenarios: `catch (\Throwable) {}`.

### Controllers
- Route model binding for `VirtualHost $virtualHost` in show/edit/update/destroy/toggle.
- Inject services in method signatures (not constructor): `public function store(StoreRequest $req, HostsFileService $hosts)`.
- `StoreVirtualHostRequest` for validation — `authorize()` returns `true`, rules in `rules()`, messages in `messages()`.
- One controller action per responsibility. No `__invoke` used.

### Models (Eloquent)
- `$fillable`, not `$guarded`.
- `protected function casts(): array` for type casting (boolean, integer).
- Scopes as `public function scopeActive($query)`.
- `use HasFactory` for factories.

### Views (Blade)
- `@extends('layouts.app')` with `@section('title', ...)` and `@section('content')`.
- Tailwind CSS utility classes. No custom CSS files — inline `<style>` in layout only.
- Font Awesome 6 for icons (`<i class="fas fa-..."></i>`).
- No semicolons in Blade `@php` blocks.
- Session flash messages use pipe-delimited format: `"main message|secondary detail"`.

### Windows-specific
- Paths use forward slashes: `C:/Apache24/logs/error.log` (PHP handles them).
- File permission errors suggest running `fix-permissions.bat` as Admin.
- Apache service management via `net start/stop`, `httpd -k restart`, and PowerShell elevation.
- `.bat` files in repo root for permission fix and restart.

### Services
- Read config in constructor: `$this->vhostsFile = config('virtualhosts.apache_vhosts_file')`.
- Facade `File` for filesystem (not `Storage` facade).
- `exec()` with `2>&1` for shell commands. `Symfony\Component\Process\Process` for complex CLI calls.
- Methods return `array` with `success` and `output` keys for non-critical paths.
- Throw `RuntimeException` only for unrecoverable errors.

### Security
- `ADMIN_PASSWORD` as bcrypt hash in `.env`. Empty string disables auth.
- Session-based admin auth, no user model. Middleware `AdminAuth` checks session.
- Throttle on login: `->middleware('throttle:5,1')`.
- Validate server names with regex `/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/`.
- `Cache::forget('apache_running')` after restart to refresh status.

### Packages
- Laravel 13.x, PHP ^8.3.
- Testing: PHPUnit 12.x, Mockery, Collision.
- Frontend: Vite 8.x, TailwindCSS 4.x, Alpine not used.
- No Pest. No Larastan. No IDE helper.
