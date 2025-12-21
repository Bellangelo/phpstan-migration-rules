<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use Phinx\Migration\AbstractMigration;

final class NonTableMethod extends AbstractMigration
{
    public function change(): void
    {
        // Rule only cares about 'table' calls
        $this->addColumn('users', 'name', 'string');
    }

    private function addColumn(string $table, string $column, string $type): void
    {
        // pretend implementation
    }
}
