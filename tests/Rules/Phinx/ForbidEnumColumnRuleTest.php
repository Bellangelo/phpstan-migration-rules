<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\ForbidEnumColumnRule;
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

    public function testReportsEnumColumnType(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidEnumColumn.php'],
            [
                [
                    'Forbidden: enum column type. Why: adding or removing enum values requires a full ALTER TABLE, which can cause long locks on large tables. Fix: use a string column with application-level validation instead.',
                    15,
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

    public function testDoesNotReportOutsidePhinxMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/NonMigrationClass.php'],
            []
        );
    }
}
