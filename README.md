# PHPStan Rules for Database Migrations

A collection of PHPStan rules to enforce best practices and standards in database migration files for various frameworks and tools.

## Installation

```bash
composer require --dev bellangelo/phpstan-migration-rules
```

The extension will be automatically registered if you have `phpstan/extension-installer` installed. Otherwise, add it manually to your `phpstan.neon`:

```neon
includes:
    - vendor/bellangelo/phpstan-migration-rules/extension.neon
```

## Rules

### Phinx

### EnforceCollationRule

Enforces that all Phinx `table()` method calls specify a collation (default: `utf8`)
```yaml
parameters:
    phpstanMigrationRules:
        phinx:
            requiredCollation: utf8mb4
```

---

### ForbidAfterRule

Forbids using the after column option in Phinx addColumn() calls, because it can trigger a full table rewrite (unsafe for large or production tables).

No configuration is required.

---

### ForbidMultipleTableCreationsRule

Forbids creating more than one table in a single Phinx migration.

A table creation is detected via calls to `create()` on table instances.

No configuration is required.

---

### Laravel

### ForbidAfterRule

Forbids using Laravel’s `after()` column modifier in migrations.

Using `after()` can force a full table rewrite or long locks (engine-dependent), which is unsafe for large or production tables.

No configuration is required.

---

### ForbidMultipleTableCreationsRule

Forbids creating more than one table in a single Laravel migration.

A table creation is detected via multiple Schema::create() calls inside the same migration.

No configuration is required.