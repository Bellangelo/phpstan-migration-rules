<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class TableWithCorrectCollation extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', [
            'collation' => 'utf8',
        ]);
    }
}
