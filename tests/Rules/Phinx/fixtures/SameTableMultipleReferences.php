<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class SameTableMultipleReferences extends AbstractMigration
{
    public function change(): void
    {
        if ($this->table('users')->hasColumn('nickname')) {
            return;
        }

        $this->table('users')
             ->addColumn('nickname', 'string', [
                 'default' => '',
                 'null' => false,
             ])
             ->update();
    }
}
