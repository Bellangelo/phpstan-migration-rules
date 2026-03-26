<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Laravel;

use PhpStanMigrationRules\Rules\Laravel\ForbidRawSqlRule;
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

    public function testReportsStatementAndUnprepared(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidRawSql.php'],
            [
                [
                    'Forbidden: raw SQL via DB::statement(). Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. Fix: use Laravel schema builder methods instead.',
                    14,
                ],
                [
                    'Forbidden: raw SQL via DB::unprepared(). Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. Fix: use Laravel schema builder methods instead.',
                    15,
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
