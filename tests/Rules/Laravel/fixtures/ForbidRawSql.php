<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

final class ForbidRawSql extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users ADD COLUMN age INT');
        DB::unprepared('CREATE TRIGGER ...');
    }
}
