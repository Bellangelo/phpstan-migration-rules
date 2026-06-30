<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends PhinxRule<ClassMethod>
 */
final class NoDownMethodRule extends PhinxRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'phinx.schema.noDownMethod';

    /**
     * @var string
     */
    private const MESSAGE =
        'Forbidden: "down" method. '
        . 'Why: a "down" method enables rollbacks, which can cause data loss and break forward-only migration strategies. '
        . 'Fix: use the "change" method for reversible migrations, or omit the rollback path entirely.';

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
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
