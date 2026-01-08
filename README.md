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
Enforces that table definitions explicitly define a collation.
> Prevents relying on database defaults, which may differ between environments.

#### Configuration

```yaml
parameters:
    phpstanMigrationRules:
        requiredCollation: utf8mb4 # Default is utf8
```

#### Support

| Framework | How collation is detected |
|---|---|
| [Phinx](./src/Rules/Phinx/EnforceCollationRule.php) | `table('name', ['collation' => '…'])` | [Phinx/EnforceCollationRule](./src/Rules/Phinx/EnforceCollationRule.php) |
| [Laravel]((./src/Rules/Laravel/EnforceCollationRule.php)) | `$table->collation('…')` or `$table->collation = '…'` inside the Blueprint callback |

---

### Rule: `ForbidAfterRule`
Forbids column positioning via `after`.
> May trigger full table rewrites or long locks, unsafe for large or production tables.

#### Support

| Framework | Forbidden usage |
|---|---|
| [Phinx](./src/Rules/Phinx/ForbidAfterRule.php) | `addColumn(..., ['after' => 'column'])` |
| [Laravel](./src/Rules/Laravel/ForbidAfterRule.php) | `$table->string('x')->after('y')` |

---

### Rule: `ForbidMultipleTableCreationsRule`
Ensures each migration creates at most one table.
> Improves rollback safety and migration clarity

---

#### Support

| Framework | What counts as a table creation |
|---|---|
| [Phinx](./src/Rules/Phinx/ForbidMultipleTableCreationsRule.php) | Multiple calls to `create()` on table instances |
| [Laravel](./src/Rules/Laravel/ForbidMultipleTableCreationsRule.php) | Multiple `Schema::create()` calls in the same migration |

---

### Rule: `NoDownMethodRule`
Forbids the usage of the `down` method in migrations.
> Useful for teams that prefer forward-only migrations or rely solely on the `change` method for extensive rollback support where possible.

#### Support

| Framework | Forbidden usage |
|---|---|
| [Phinx](./src/Rules/Phinx/NoDownMethodRule.php) | `public function down(): void` |
| [Laravel](./src/Rules/Laravel/NoDownMethodRule.php) | `public function down(): void` |

