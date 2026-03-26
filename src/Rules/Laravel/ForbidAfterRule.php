<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @extends LaravelRule<MethodCall>
 */
final class ForbidAfterRule extends LaravelRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'laravel.schema.afterForbidden';

    /**
     * @var string
     */
    private const MESSAGE =
        'Forbidden: column positioning ("after"). '
        . 'Why: can trigger a full table rewrite or long locks depending on the engine. '
        . 'Fix: avoid column ordering in migrations.';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isLaravelMigration($scope)) {
            return [];
        }

        if (!$this->isAfterCall($node)) {
            return [];
        }

        // Only fire when PHPStan can prove this is ColumnDefinition->after()
        $receiverType = $scope->getType($node->var);
        $columnDefinitionType = new ObjectType(\Illuminate\Database\Schema\ColumnDefinition::class);

        if (!$columnDefinitionType->isSuperTypeOf($receiverType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
            ->identifier(self::RULE_IDENTIFIER)
            ->build(),
        ];
    }

    private function isAfterCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'after';
    }
}
