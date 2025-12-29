<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class ForbidAfterMigration extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('username', 'string');

        // Violation should be reported on the next line (line 15):
        $table->addColumn('email', 'string', ['after' => 'username']);

        $table->update();
    }
}
