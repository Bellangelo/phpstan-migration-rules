<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class AllowSingleTableCreationMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')->create();
    }
}
