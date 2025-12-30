<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\EnforceCollationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<EnforceCollationRule>
 */
final class EnforceCollationRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EnforceCollationRule('utf8');
    }

    public function testTableWithoutOptions(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/TableWithoutOptions.php',
            ],
            [
                [
                    'Required: table collation must be "utf8". Why: prevents environment-dependent defaults and keeps schema consistent. Fix: set the table collation explicitly in the migration.',
                    13,
                ],
            ]
        );
    }

    public function testTableWithNonArrayOptions(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/TableWithNonArrayOptions.php',
            ],
            [
                [
                    'Required: table collation must be "utf8". Why: prevents environment-dependent defaults and keeps schema consistent. Fix: set the table collation explicitly in the migration.',
                    14,
                ],
                [
                    'No error with identifier argument.type is reported on line 14.',
                    14
                ],
            ]
        );
    }

    public function testTableWithoutCollationKey(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/TableWithoutCollationKey.php',
            ],
            [
                [
                    'Required: table collation must be "utf8". Why: prevents environment-dependent defaults and keeps schema consistent. Fix: set the table collation explicitly in the migration.',
                    13,
                ],
            ]
        );
    }

    public function testTableWithWrongCollation(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/TableWithWrongCollation.php',
            ],
            [
                [
                    'Required: table collation must be "utf8". Found: "utf8mb4_unicode_ci". Why: prevents environment-dependent defaults and keeps schema consistent. Fix: set the table collation explicitly in the migration.',
                    13,
                ],
            ]
        );
    }

    public function testTableWithCorrectCollation(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/TableWithCorrectCollation.php',
            ],
            []
        );
    }

    public function testDoesNotReportOutsidePhinxMigration(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/NonMigrationClass.php',
            ],
            []
        );
    }

    public function testNonTableMethodIsIgnored(): void
    {
        $this->analyse(
            [
                __DIR__ . '/fixtures/NonTableMethod.php',
            ],
            []
        );
    }
}
