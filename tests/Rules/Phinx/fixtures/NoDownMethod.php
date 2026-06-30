<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\fixtures;

use Phinx\Migration\AbstractMigration;

class NoDownMethod extends AbstractMigration
{
    public function down(): void
    {
        // ...
    }
}
