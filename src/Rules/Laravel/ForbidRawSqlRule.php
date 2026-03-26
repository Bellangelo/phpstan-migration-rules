<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends LaravelRule<StaticCall>
 */
final class ForbidRawSqlRule extends LaravelRule
{
    private const string RULE_IDENTIFIER = 'laravel.schema.rawSqlForbidden';

    private const string MESSAGE =
        'Forbidden: raw SQL via DB::%s(). '
        . 'Why: raw SQL bypasses the schema builder, making migrations harder to review, less portable, and prone to errors. '
        . 'Fix: use Laravel schema builder methods instead.';

    private const array FORBIDDEN_METHODS = ['statement', 'unprepared'];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isLaravelMigration($scope)) {
            return [];
        }

        $methodName = $this->getForbiddenMethodName($node, $scope);
        if ($methodName === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(self::MESSAGE, $methodName))
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function getForbiddenMethodName(StaticCall $node, Scope $scope): ?string
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }

        if (!$node->class instanceof Name) {
            return null;
        }

        $resolved = $scope->resolveName($node->class);
        if ($resolved !== \Illuminate\Support\Facades\DB::class) {
            return null;
        }

        $name = $node->name->toString();

        return in_array($name, self::FORBIDDEN_METHODS, true) ? $name : null;
    }
}
