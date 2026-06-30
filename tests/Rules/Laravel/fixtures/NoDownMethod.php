<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\fixtures;

use Illuminate\Database\Migrations\Migration;

class NoDownMethod extends Migration
{
    public function down(): void
    {
        // ...
    }
}
