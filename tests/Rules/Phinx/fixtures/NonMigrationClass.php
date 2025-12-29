<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx\Fixtures;

final class NonMigrationClass
{
    public function change(): void
    {
        // Looks similar, but not inside AbstractMigration
        $this->table('users', [
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $this->table('users', [
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        
        $this->addColumn('my_column', [
            'after' => 'my_other_column'
        ]);
    }

    /**
     * @param string $name
     * @param array<string, mixed> $options
     */
    private function table(string $name, array $options = []): void
    {
        // local helper, not Phinx
    }

    /**
     * @param string $name
     * @param array<string, mixed> $options
     */
    private function addColumn(string $name, array $options = []): void
    {
        // local helper, not Phinx
    }
}
