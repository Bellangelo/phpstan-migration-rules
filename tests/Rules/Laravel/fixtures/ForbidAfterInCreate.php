<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ForbidAfterInCreate extends Migration
{
    public function up(): void
    {
        // We not have the whole Laravel installed.
        // Which means that we do not have Facade support.
        $schema = new class {
            public function create(string $table, callable $callback): void
            {
                /** @phpstan-ignore argument.type */
                $callback(new Blueprint($table, 'random', null));
            }
        };

        $schema->create('users', function (Blueprint $table): void {
            $table->string('email')->after('username');
        });
    }
}
