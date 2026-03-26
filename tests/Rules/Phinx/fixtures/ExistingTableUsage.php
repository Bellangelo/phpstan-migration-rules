<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class ExistingTableUsage extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('name', 'string')
            ->update();
    }
}
