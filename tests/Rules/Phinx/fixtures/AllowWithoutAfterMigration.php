<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class AllowWithoutAfterMigration extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('username', 'string');
        $table->addColumn('email', 'string');

        $table->update();
    }
}
