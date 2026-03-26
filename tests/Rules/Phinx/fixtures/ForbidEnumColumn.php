<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class ForbidEnumColumn extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('status', 'enum', ['values' => ['active', 'inactive']]);

        $table->update();
    }
}
