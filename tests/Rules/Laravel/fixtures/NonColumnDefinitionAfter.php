<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;

final class NonColumnDefinitionAfter extends Migration
{
    public function up(): void
    {
        $step = new class {
            public function after(string $something): void
            {
            }
        };

        $step->after('anything');
    }
}
