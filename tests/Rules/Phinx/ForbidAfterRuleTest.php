<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\ForbidAfterRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidAfterRule>
 */
final class ForbidAfterRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidAfterRule();
    }

    public function testReportsAfterOptionInAddColumn(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidAfterMigration.php'],
            [
                [
                    'Using the "after" column option in migrations is forbidden. It forces a full table rewrite, which is unsafe for large or production tables.',
                    18,
                ],
            ]
        );
    }

    public function testAllowsAddColumnWithoutAfterOption(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowWithoutAfterMigration.php'],
            []
        );
    }

    public function testDoesNotReportOutsidePhinxMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/NonMigrationClass.php'],
            []
        );
    }
}
