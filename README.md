# PHPStan Rules for Database Migrations

A collection of PHPStan rules to enforce best practices and standards in database migration files for Phinx and Laravel / Illuminate.

## Installation

```bash
composer require --dev bellangelo/phpstan-migration-rules
```

The extension will be automatically registered if you have `phpstan/extension-installer` installed. Otherwise, add it manually to your `phpstan.neon`:

```neon
includes:
    - vendor/bellangelo/phpstan-migration-rules/extension.neon
```

## Configuration

All rules are enabled by default. You can customize behavior in your `phpstan.neon`:

```neon
parameters:
    migrationRules:
        requiredCollation: utf8mb4 # Default is utf8

        phinx:
            enforceCollation: true
            forbidAfter: true
            forbidEnumColumn: true
            forbidMultipleTableCreations: true

        laravel:
            enforceCollation: true
            forbidAfter: true
            forbidEnumColumn: true
            forbidMultipleTableCreations: true
```

If you only use one framework, disable the other to avoid unnecessary processing:

```neon
parameters:
    migrationRules:
        phinx:
            enforceCollation: false
            forbidAfter: false
            forbidEnumColumn: false
            forbidMultipleTableCreations: false
```

## Rule catalog

Each rule below applies to migration files for both Phinx and Laravel, unless stated otherwise.

### Rule: `EnforceCollationRule`
Enforces that table definitions explicitly define a collation.
> Prevents relying on database defaults, which may differ between environments.

| Framework | How collation is detected |
|---|---|
| [Phinx](./src/Rules/Phinx/EnforceCollationRule.php) | `table('name', ['collation' => '…'])` |
| [Laravel](./src/Rules/Laravel/EnforceCollationRule.php) | `$table->collation('…')` or `$table->collation = '…'` inside the Blueprint callback |

---

### Rule: `ForbidAfterRule`
Forbids column positioning via `after`.
> May trigger full table rewrites or long locks, unsafe for large or production tables.

| Framework | Forbidden usage |
|---|---|
| [Phinx](./src/Rules/Phinx/ForbidAfterRule.php) | `addColumn(..., ['after' => 'column'])` |
| [Laravel](./src/Rules/Laravel/ForbidAfterRule.php) | `$table->string('x')->after('y')` |

---

### Rule: `ForbidEnumColumnRule`
Forbids the use of `enum` column types in migrations.
> Modifying enum values requires a full `ALTER TABLE`, which can cause long locks on large tables. Use a string column with application-level validation instead.

| Framework | Forbidden usage |
|---|---|
| [Phinx](./src/Rules/Phinx/ForbidEnumColumnRule.php) | `addColumn('col', 'enum', ['values' => [...]])` |
| [Laravel](./src/Rules/Laravel/ForbidEnumColumnRule.php) | `$table->enum('col', [...])` |

---

### Rule: `ForbidMultipleTableCreationsRule`
Ensures each migration creates at most one table.
> Improves rollback safety and migration clarity.

| Framework | What counts as a table creation |
|---|---|
| [Phinx](./src/Rules/Phinx/ForbidMultipleTableCreationsRule.php) | Multiple calls to `create()` on table instances |
| [Laravel](./src/Rules/Laravel/ForbidMultipleTableCreationsRule.php) | Multiple `Schema::create()` calls in the same migration |
