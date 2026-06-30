<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel\fixtures;

use Illuminate\Database\Migrations\Migration;

class WithChangeMethod extends Migration
{
    public function change(): void
    {
        // ...
    }
}
