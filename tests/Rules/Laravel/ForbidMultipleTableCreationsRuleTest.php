<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel;

use PhpStanMigrationRules\Rules\Laravel\ForbidMultipleTableCreationsRule;
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

    public function testReportsSecondSchemaCreateInMigration(): void
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

    public function testAllowsSingleSchemaCreateInMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowSingleTableCreation.php'],
            []
        );
    }

    public function testDoesNotReportOutsideLaravelMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/NonMigrationClass.php'],
            [
                [
                    'No error to ignore is reported on line 15.',
                    15,
                ]
            ],
        );
    }
}
