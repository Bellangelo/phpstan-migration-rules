<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends LaravelRule<ClassMethod>
 */
final class NoDownMethodRule extends LaravelRule
{
    private const string RULE_IDENTIFIER = 'laravel.schema.noDownMethod';

    private const string MESSAGE = 'Forbidden: "down" method. Use "change" method for reversible migrations, or forward-only migrations.';

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isLaravelMigration($scope)) {
            return [];
        }

        if ($node->name->toString() !== 'down') {
            return [];
        }

        if (!$node->isPublic()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier(self::RULE_IDENTIFIER)
                ->tip('If you must rollback, consider creating a new migration that reverses these changes.')
                ->build(),
        ];
    }
}
