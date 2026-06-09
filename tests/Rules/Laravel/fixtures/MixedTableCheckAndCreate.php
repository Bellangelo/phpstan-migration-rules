<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class MixedTableCheckAndCreate extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->string('email');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('name');
        });
    }
}
