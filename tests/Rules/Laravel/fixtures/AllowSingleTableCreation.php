<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

final class AllowSingleTableCreation extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (): void {
        });
    }
}
