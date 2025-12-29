<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class WrongCollation extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->collation('utf8');
            $table->string('email');
        });
    }
}
