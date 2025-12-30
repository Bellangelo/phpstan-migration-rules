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
     * @var array<string, int>
     */
    private array $tableCallsPerClass = [];

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

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        $className = $classReflection->getName();
        $this->tableCallsPerClass[$className] = ($this->tableCallsPerClass[$className] ?? 0) + 1;

        if ($this->tableCallsPerClass[$className] > 1) {
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
}
