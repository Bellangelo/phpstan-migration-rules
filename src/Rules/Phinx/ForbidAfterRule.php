<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends PhinxRule<MethodCall>
 */
final class ForbidAfterRule extends PhinxRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'phinx.schema.afterForbidden';

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
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        if (!$this->isAddColumnCall($node)) {
            return [];
        }

        foreach ($node->args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }

            if (!$arg->value instanceof Array_) {
                continue;
            }

            foreach ($arg->value->items as $item) {
                $key = $item->key;

                if (!$key instanceof String_) {
                    continue;
                }

                if ($key->value === 'after') {
                    return [
                        RuleErrorBuilder::message(self::MESSAGE)
                            ->identifier(self::RULE_IDENTIFIER)
                            ->build(),
                    ];
                }
            }
        }

        return [];
    }

    private function isAddColumnCall(MethodCall $node): bool
    {
        return $node->name instanceof Node\Identifier
            && $node->name->toString() === 'addColumn';
    }
}
