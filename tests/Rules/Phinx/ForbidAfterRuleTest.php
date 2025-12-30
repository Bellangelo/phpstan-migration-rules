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
                    'Forbidden: column positioning ("after"). Reason: can trigger a full table rewrite or long locks depending on the engine. Fix: avoid column ordering in migrations.',
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
