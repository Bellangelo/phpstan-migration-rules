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

## Rule catalog

Each rule below applies to migration files, regardless of framework, unless stated otherwise.

### Rule: `EnforceCollationRule`

| Field | Description |
|---|---|
| Purpose | Enforces that table definitions explicitly define a collation |
| Why | Prevents relying on database defaults, which may differ between environments |
| Framework support | Phinx, Laravel |
| Default behavior | Requires a collation to be specified |
| Configuration | `requiredCollation` (string) |

#### Configuration

```yaml
parameters:
    phpstanMigrationRules:
        requiredCollation: utf8mb4
```

#### Detection details

| Framework | How collation is detected |
|---|---|
| Phinx | `table('name', ['collation' => '…'])` |
| Laravel | `$table->collation('…')` or `$table->collation = '…'` inside the Blueprint callback |

---

### Rule: `ForbidAfterRule`

| Field | Description |
|---|---|
| Purpose | Forbids column positioning via `after()` |
| Why | May trigger full table rewrites or long locks, unsafe for large or production tables |
| Framework support | Phinx, Laravel |
| Configuration | None |

#### Detection details

| Framework | Forbidden usage |
|---|---|
| Phinx | `addColumn(..., ['after' => 'column'])` |
| Laravel | `$table->string('x')->after('y')` |

---

### Rule: `ForbidMultipleTableCreationsRule`

| Field | Description |
|---|---|
| Purpose | Ensures each migration creates at most one table |
| Why | Improves rollback safety and migration clarity |
| Framework support | Phinx, Laravel |
| Configuration | None |

---

#### Detection details

| Framework | What counts as a table creation |
|---|---|
| Phinx | Multiple calls to `create()` on table instances |
| Laravel | Multiple `Schema::create()` calls in the same migration |
