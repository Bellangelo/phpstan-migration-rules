<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests\Rules\Phinx;

use PhpStanMigrationRules\Rules\Phinx\ForbidMultipleTableCreationsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class ForbidMultipleTableCreationsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidMultipleTableCreationsRule();
    }

    public function testReportsSecondTableCreateInMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/ForbidMultipleTableCreations.php'],
            [
                [
                    'Creating multiple tables in a single Phinx migration is forbidden. Each migration should create exactly one table.',
                    16,
                ],
            ]
        );
    }

    public function testAllowsSingleTableCreateInMigration(): void
    {
        $this->analyse(
            [__DIR__ . '/fixtures/AllowSingleTableCreation.php'],
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
