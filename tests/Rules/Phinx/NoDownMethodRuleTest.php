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
                    'Forbidden: "down" method. Why: a "down" method enables rollbacks, which can cause data loss and break forward-only migration strategies. Fix: use the "change" method for reversible migrations, or omit the rollback path entirely.',
                    11,
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
