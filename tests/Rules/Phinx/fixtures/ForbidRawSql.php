<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

use Phinx\Migration\AbstractMigration;

final class ForbidRawSql extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('ALTER TABLE users ADD COLUMN age INT');
        $this->query('SELECT * FROM users');
    }
}
