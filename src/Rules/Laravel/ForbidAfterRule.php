<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
final class ForbidAfterRule implements Rule
{
    private const RULE_IDENTIFIER = 'laravel.schema.afterForbidden';

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

        // Only fire when PHPStan can prove this is a ColumnDefinition->after()
        $receiverType = $scope->getType($node->var);
        $columnDefinitionType = new ObjectType(\Illuminate\Database\Schema\ColumnDefinition::class);

        if (!$receiverType->isSuperTypeOf($columnDefinitionType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Using "after()" in migrations is forbidden. '
                . 'It forces a full table rewrite or long locks depending on the engine, which is unsafe for large or production tables.'
            )
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function isAfterCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'after';
    }

    private function isLaravelMigration(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        return $classReflection->isSubclassOf(\Illuminate\Database\Migrations\Migration::class);
    }
}
