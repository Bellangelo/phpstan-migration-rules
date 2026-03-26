<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\ForbidRawSqlRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidRawSqlRule>
 */
final class ForbidRawSqlRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidRawSqlRule();
    }

    public function testReportsExecuteAndQuery(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidRawSql.php'],
            [
                [
                    'Forbidden: raw SQL via execute(). Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. Fix: use Phinx schema builder methods instead.',
                    13,
                ],
                [
                    'Forbidden: raw SQL via query(). Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. Fix: use Phinx schema builder methods instead.',
                    14,
                ],
            ]
        );
    }

    public function testAllowsSchemaBuilderMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowSchemaBuilder.php'],
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
