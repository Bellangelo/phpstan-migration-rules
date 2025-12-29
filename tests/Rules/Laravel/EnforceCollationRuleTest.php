<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel;

use PhpStanMigrationRules\Rules\Laravel\EnforceCollationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<EnforceCollationRule>
 */
final class EnforceCollationRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EnforceCollationRule('utf8mb4');
    }

    public function testReportsMissingCollation(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/MissingCollation.php'],
            [
                [
                    'Laravel migrations must set table collation to "utf8mb4" in Schema::create().',
                    15,
                ],
            ]
        );
    }

    public function testReportsWrongCollation(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/WrongCollation.php'],
            [
                [
                    'Laravel migrations must set table collation to "utf8mb4" in Schema::create().',
                    15,
                ],
            ]
        );
    }

    public function testAllowsCorrectCollationTopLevel(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowCollation.php'],
            []
        );
    }

    public function testAllowsCorrectCollationViaPropertyAssignment(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowCollationPropertyAssignment.php'],
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
