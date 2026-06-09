<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class AllowSchemaBuilder extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('age', 'integer');

        $table->update();
    }
}
