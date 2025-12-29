<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

final class ForbidMultipleTableCreations extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (): void {
        });
        Schema::create('courses', static function (): void {
        });
    }
}
