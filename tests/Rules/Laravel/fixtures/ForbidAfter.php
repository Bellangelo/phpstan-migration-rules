<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ForbidAfter extends Migration
{
    public function up(): void
    {
        /** @phpstan-ignore argument.type */
        $table = new Blueprint('users', 'random', null);

        $table->string('email')->after('username');
    }
}
