<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class AllowNonEnumColumn extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('status', 'string');
        $table->addColumn('age', 'integer');

        $table->update();
    }
}
