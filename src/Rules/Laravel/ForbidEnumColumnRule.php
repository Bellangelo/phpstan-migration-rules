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
final class ForbidEnumColumnRule extends LaravelRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'laravel.schema.enumColumnForbidden';

    /**
     * @var string
     */
    private const MESSAGE =
        'Forbidden: enum column type. '
        . 'Why: adding or removing enum values requires a full ALTER TABLE, which can cause long locks on large tables. '
        . 'Fix: use a string column with application-level validation instead.';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isLaravelMigration($scope)) {
            return [];
        }

        if (!$this->isEnumCall($node)) {
            return [];
        }

        $receiverType = $scope->getType($node->var);
        $blueprintType = new ObjectType(\Illuminate\Database\Schema\Blueprint::class);

        if (!$blueprintType->isSuperTypeOf($receiverType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function isEnumCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'enum';
    }
}
