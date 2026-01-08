<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\NoDownMethodRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoDownMethodRule>
 */
final class NoDownMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoDownMethodRule();
    }

    public function testReportsDownMethod(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/NoDownMethod.php'],
            [
                [
                    'Forbidden: "down" method. Use "change" method for reversible migrations, or forward-only migrations.',
                    11,
                    'If you must rollback, consider creating a new migration that reverses these changes.',
                ],
            ]
        );
    }

    public function testDoesNotReportChangeMethod(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/WithChangeMethod.php'],
            []
        );
    }
}
