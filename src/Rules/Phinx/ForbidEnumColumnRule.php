<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends PhinxRule<MethodCall>
 */
final class ForbidEnumColumnRule extends PhinxRule
{
    private const string RULE_IDENTIFIER = 'phinx.schema.enumColumnForbidden';

    private const string MESSAGE =
        'Forbidden: enum column type. '
        . 'Why: adding or removing enum values requires a full ALTER TABLE, which can cause long locks on large tables. '
        . 'Fix: use a string column with application-level validation instead.';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        if (!$this->isAddColumnCall($node)) {
            return [];
        }

        if (!$this->hasEnumType($node)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function isAddColumnCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'addColumn';
    }

    private function hasEnumType(MethodCall $node): bool
    {
        $args = $node->getArgs();

        if (count($args) < 2) {
            return false;
        }

        $typeArg = $args[1]->value;

        return $typeArg instanceof String_ && $typeArg->value === 'enum';
    }
}
