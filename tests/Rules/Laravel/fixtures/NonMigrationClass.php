<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Schema\Blueprint;

final class NonMigrationClass
{
    public function run(): void
    {
        /** @phpstan-ignore argument.type */
        $table = new Blueprint('users', 'random', null);

        $table->string('email')->after('username');
    }
}
