<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class ForbidMultipleTableCreationsMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')->create();

        // Violation should be reported on the next line (line 16):
        $this->table('courses')->create();
    }
}
