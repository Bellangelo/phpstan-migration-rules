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

- **[EnforceCollationRule](src/Rules/Phinx/EnforceCollationRule.php)** - Enforces that all Phinx `table()` method calls specify a collation (default: `utf8`)

    #### Configuration example:
    ```yaml
    parameters:
        phpstanMigrationRules:
            phinx:
                requiredCollation: utf8mb4
    ```

### Laravel

Rules for Laravel Migrations are coming soon.
