<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel;

use PhpStanMigrationRules\Rules\Laravel\ForbidAfterRule;
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

    public function testReportsAfterInFluentChain(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidAfter.php'],
            [
                [
                    'Using "after()" in migrations is forbidden. It forces a full table rewrite or long locks depending on the engine, which is unsafe for large or production tables.',
                    17,
                ],
                [
                    'No error to ignore is reported on line 15.',
                    15,
                ],
            ]
        );
    }

    public function testReportsAfterInsideCreateClosure(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidAfterInCreate.php'],
            [
                [
                    'Using "after()" in migrations is forbidden. It forces a full table rewrite or long locks depending on the engine, which is unsafe for large or production tables.',
                    25,
                ],
                [
                    'No error to ignore is reported on line 20.',
                    20,
                ],
            ]
        );
    }

    public function testDoesNotReportOutsideLaravelMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/NonMigrationClass.php'],
            [
                [
                    'No error to ignore is reported on line 14.',
                    14,
                ]
            ],
        );
    }
}
