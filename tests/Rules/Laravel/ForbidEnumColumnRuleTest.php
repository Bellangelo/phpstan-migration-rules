<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel;

use PhpStanMigrationRules\Rules\Laravel\ForbidEnumColumnRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidEnumColumnRule>
 */
final class ForbidEnumColumnRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidEnumColumnRule();
    }

    public function testReportsEnumColumnInCreateClosure(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidEnumColumn.php'],
            [
                [
                    'Forbidden: enum column type. Why: adding or removing enum values requires a full ALTER TABLE, which can cause long locks on large tables. Fix: use a string column with application-level validation instead.',
                    16,
                ],
            ]
        );
    }

    public function testAllowsNonEnumColumnTypes(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowNonEnumColumn.php'],
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
                ],
            ],
        );
    }
}
