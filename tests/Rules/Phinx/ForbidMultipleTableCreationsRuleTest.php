<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\ForbidMultipleTableCreationsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidMultipleTableCreationsRule>
 */
final class ForbidMultipleTableCreationsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidMultipleTableCreationsRule();
    }

    public function testReportsSecondTableCreateInMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidMultipleTableCreations.php'],
            [
                [
                    'Forbidden: creating multiple tables in a single migration. Why: reduces reviewability and rollback safety. Fix: split into one migration per table.',
                    16,
                ],
            ]
        );
    }

    public function testAllowsSingleTableCreateInMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowSingleTableCreation.php'],
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
