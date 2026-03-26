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
final class ForbidRawSqlRule extends PhinxRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'phinx.schema.rawSqlForbidden';

    /**
     * @var string
     */
    private const MESSAGE =
        'Forbidden: raw SQL via %s(). '
        . 'Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. '
        . 'Fix: use Phinx schema builder methods instead.';

    /**
     * @var mixed[]
     */
    private const FORBIDDEN_METHODS = ['execute', 'query'];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        $methodName = $this->getForbiddenMethodName($node);
        if ($methodName === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(self::MESSAGE, $methodName))
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function getForbiddenMethodName(MethodCall $node): ?string
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }

        $name = $node->name->toString();

        return in_array($name, self::FORBIDDEN_METHODS, true) ? $name : null;
    }
}
