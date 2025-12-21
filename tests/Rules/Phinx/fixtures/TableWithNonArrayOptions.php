<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use Phinx\Migration\AbstractMigration;

final class TableWithNonArrayOptions extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', 'not-an-array');
    }
}
