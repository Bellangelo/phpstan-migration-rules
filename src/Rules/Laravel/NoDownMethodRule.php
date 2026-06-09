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

    private const string MESSAGE =
        'Forbidden: "down" method. '
        . 'Why: a "down" method enables rollbacks, which can cause data loss and break forward-only migration strategies. '
        . 'Fix: use the "change" method for reversible migrations, or omit the rollback path entirely.';

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
                ->build(),
        ];
    }
}
