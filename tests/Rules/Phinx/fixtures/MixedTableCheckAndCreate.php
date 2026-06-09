<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class MixedTableCheckAndCreate extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('users')) {
            $this->table('users')->create();
        }
    }
}
