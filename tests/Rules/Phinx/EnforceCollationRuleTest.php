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
        // Default required collation is `utf8`
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
                    'Phinx table() method must specify collation. Expected collation: "utf8"',
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
                    'Phinx table() method options must be an array with collation set to "utf8"',
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
                    'Phinx table() method must specify collation option. Expected: "utf8"',
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
                    'Phinx table() method collation must be "utf8", got "utf8mb4_unicode_ci"',
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
