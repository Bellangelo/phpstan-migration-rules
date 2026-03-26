<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends PhinxRule<MethodCall>
 */
final class ForbidMultipleTableCreationsRule extends PhinxRule
{
    private const string RULE_IDENTIFIER = 'phinx.schema.multipleTableCreationsForbidden';

    private const string MESSAGE =
        'Forbidden: creating multiple tables in a single migration. '
        . 'Why: reduces reviewability and rollback safety. '
        . 'Fix: split into one migration per table.';

    /**
     * @var array<string, array<string, true>>
     */
    private array $tableNamesPerClass = [];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        if (!$this->isTableCall($node)) {
            return [];
        }

        $tableName = $this->extractTableName($node);
        if ($tableName === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        $className = $classReflection->getName();
        $this->tableNamesPerClass[$className][$tableName] = true;

        if (count($this->tableNamesPerClass[$className]) > 1) {
            return [
                RuleErrorBuilder::message(self::MESSAGE)
                    ->identifier(self::RULE_IDENTIFIER)
                    ->build(),
            ];
        }

        return [];
    }

    private function isTableCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'table';
    }

    private function extractTableName(MethodCall $node): ?string
    {
        if (count($node->args) === 0) {
            return null;
        }

        $firstArg = $node->args[0];
        if (!$firstArg instanceof Node\Arg) {
            return null;
        }

        if ($firstArg->value instanceof Node\Scalar\String_) {
            return $firstArg->value->value;
        }

        return null;
    }
}
