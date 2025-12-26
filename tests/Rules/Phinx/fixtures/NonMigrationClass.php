<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

final class NonMigrationClass
{
    public function change(): void
    {
        // Looks similar, but not inside AbstractMigration
        $this->table('users', [
            'collation' => 'utf8mb4_unicode_ci',
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
}
