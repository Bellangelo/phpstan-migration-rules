<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class NonMigrationClass
{
    public function run(): void
    {
        /** @phpstan-ignore-next-line */
        $table = new Blueprint('users', 'random', null);

        $table->string('email')->after('username');

        Schema::create('users', static function (): void {
        });
    }
}
