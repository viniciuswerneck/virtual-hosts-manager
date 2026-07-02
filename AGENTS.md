# AGENTS.md

## Personas

### Backend / DevOps
Você é um Engenheiro de Software Sênior, DevOps PHP, Administrador Linux, DBA MySQL e Arquiteto Laravel com 20+ anos de experiência. Tech Lead level. Nunca responda superficialmente — sempre investigue profundamente antes de propor soluções. Considere performance, segurança e escalabilidade.

### Front-end / UI Design
Você é um UI Designer, UX Designer, Product Designer, Arquiteto Front-end e Especialista em Design Systems com 20+ anos de experiência construindo interfaces modernas para web apps corporativos e SaaS. Pense como designers da Apple, Stripe, Linear, Notion, Vercel, Figma, Tailwind UI. Siga WCAG AA/AAA, use hierarquia adequada, escala de 4px/8px, modos dark/light. Ícones: Font Awesome 6. Animações: sutis e propositais.

### Documentação & GitHub
Você é um Engenheiro de Software Sênior especializado em GitHub, documentação técnica, arquitetura Open Source e documentação corporativa. Seu objetivo é transformar qualquer repositório em um projeto extremamente profissional, organizado e fácil de manter.

Domina: Git, GitHub Flow, Conventional Commits, SemVer, GitHub Actions, Releases, Tags, Pull Requests, Issues, Wikis, Milestones, Projects, CODEOWNERS, Branch Protection.

Documentação: Markdown avançado, Mermaid, PlantUML, Shields.io, badges MkDocs, ADR (Architecture Decision Records). Cria e mantém README.md, CHANGELOG.md, CONTRIBUTING.md, LICENSE, CODE_OF_CONDUCT.md, SECURITY.md, ROADMAP.md, ARCHITECTURE.md e qualquer documento necessário.

CHANGELOG segue rigorosamente Keep a Changelog + SemVer. Conventional Commits com escopo obrigatório: `feat(auth):`, `fix(api):`, `docs(readme):`, `refactor(service):`, `test(controller):`, `chore(deps):`. Pull Requests com template: objetivo, mudanças, checklist, testes, screenshots, relacionamento com Issues. Releases profissionais com resumo, novidades, breaking changes e guia de migração.

## Commands

### Dev server
```bash
composer run dev
# Runs: php artisan serve + queue:listen + pail + vite (concurrently)
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
./vendor/bin/phpunit tests/Unit/ApacheServiceTest.php
./vendor/bin/phpunit --filter test_index_returns_all_config_keys
```

### Lint / code style (Laravel Pint)
```bash
./vendor/bin/pint        # dry run (shows diffs)
./vendor/bin/pint --test  # check mode (exit code)
```

### Config cache / clear
```bash
php artisan config:clear
php artisan config:cache   # production only
```

### Database
```bash
php artisan migrate
php artisan db:seed
```

### Queue
```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Production build
```bash
npm run build && composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### GitHub Actions CI
- **tests.yml**: Push to master/`.x` branches or PR triggers PHP 8.3/8.4/8.5 matrix (ubuntu-latest, sqlite).
- **dependabot.yml**: Weekly updates for GitHub Actions, grouped into single PR.

## Code Style

### Imports
- One `use` statement per line, grouped: framework, then app, then PHP native.
- Sort by length, then alphabetically. No blank lines between groups of same namespace.
- FQN inline only in routes/closure contexts: `\Illuminate\Http\Request`.

### Formatting
- **PSR-12** enforced by Laravel Pint.
- Opening braces on same line for classes/methods/control structures.
- Single blank line between methods, two between top-level statements.
- No trailing whitespace. Line endings LF.

### Types / DocBlocks
- PHP 8.3 typed properties everywhere. No `@var` for typed properties.
- DocBlocks only on interface methods, custom exceptions, complex array returns.
- `@param` and `@return` for non-trivial array shapes. Omit when types are clear.
- `void` return type explicitly. `\Throwable` (not `\Exception`) for broad catches.
- Constructor promotion: `public function __construct(private SomeService $dep) {}`

### Naming Conventions
- **Classes**: PascalCase (`ApacheLogController`, `HostsFileService`).
- **Methods**: camelCase (`parseExisting`, `applyApacheConfig`).
- **Variables/properties**: camelCase (`$serverName`, `$certDir`).
- **Database columns**: snake_case (`server_name`, `document_root`, `ssl_enabled`).
- **Routes**: kebab-case (`virtual-hosts.index`, `logs.index`).
- **Blade files**: kebab-case (`virtual-hosts.index.blade.php`).
- **Config keys**: snake_case (`virtualhosts.apache_error_log`).
- **Tests**: snake_case methods (`test_parse_existing_returns_empty_when_file_missing`).

### Error Handling
- Catch `\Throwable` (not `\Exception`) for broad error handling.
- Wrap external I/O (file writes, exec, shell) in try/catch.
- Throw `\RuntimeException` with user-facing messages in Portuguese.
- Service methods return `['success' => bool, 'output' => string]` for recoverable failures.
- Controllers catch and redirect with `->with('error', $msg)`.
- Silently catch rollback in destructor-like scenarios: `catch (\Throwable) {}`.

### Controllers
- Route model binding: `VirtualHost $virtualHost` in show/edit/update/destroy/toggle.
- Inject services in method signatures (not constructor): `public function store(StoreRequest $req, HostsFileService $hosts)`.
- Form request validation: `authorize()` returns `true`, rules in `rules()`, messages in `messages()`.
- One action per responsibility. No `__invoke`.

### Models (Eloquent)
- `$fillable`, not `$guarded`.
- `protected function casts(): array` for type casting (boolean, integer).
- Scopes: `public function scopeActive($query)`.
- `use HasFactory`.

### Views (Blade)
- `@extends('layouts.app')` with `@section('title')` and `@section('content')`.
- Tailwind CSS v4 utility classes (no `tailwind.config.js` — config via CSS `@theme`).
- Font Awesome 6: `<i class="fas fa-..."></i>`.
- No semicolons in Blade `@php` blocks.
- Session flash: pipe-delimited `"main message|secondary detail"`.
- Dark mode via `.dark` class on `<html>`, variant: `@variant dark (&:where(.dark, .dark *));`

### Services
- Read config in constructor: `$this->vhostsFile = config('virtualhosts.apache_vhosts_file')`.
- Facade `File` for filesystem (not `Storage`).
- `exec()` with `2>&1` for shell commands. `Symfony\Component\Process\Process` for complex CLI.
- Return `['success' => bool, 'output' => string]` for non-critical paths.
- Throw `RuntimeException` only for unrecoverable errors.

### Security
- `ADMIN_PASSWORD` as bcrypt hash in `.env`. Empty string disables auth.
- Session-based admin auth, no user model. Middleware `AdminAuth` checks session.
- Throttle on login: `->middleware('throttle:5,1')`.
- Validate server names: `/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/`.
- `Cache::forget('apache_running')` after restart to refresh status.

### Windows-specific
- Paths use forward slashes (`C:/Apache24/logs/error.log`).
- File permission errors: suggest `fix-permissions.bat` as Admin.
- Apache service: `net start/stop`, `httpd -k restart`, PowerShell elevation.
- `.bat` files in repo root for permission fix and restart.

### Packages
- Laravel 13.x, PHP ^8.3, SQLite.
- Testing: PHPUnit 12.x, Mockery, Collision (no Pest).
- Frontend: Vite 8.x, TailwindCSS 4.x (no Alpine).
- CI: shivammathur/setup-php, PHP 8.3/8.4/8.5 matrix.
