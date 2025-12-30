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
final class ForbidMultipleTableCreationsRule extends LaravelRule
{
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'laravel.schema.multipleTableCreationsForbidden';

    /**
     * @var string
     */
    private const MESSAGE =
        'Forbidden: creating multiple tables in a single migration. '
        . 'Why: reduces reviewability and rollback safety. '
        . 'Fix: split into one migration per table.';

    /**
     * @var array<string, int>
     */
    private array $createCallsPerFile = [];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isLaravelMigration($scope)) {
            return [];
        }

        if (!$this->isSchemaCreateCall($node, $scope)) {
            return [];
        }

        $file = $scope->getFile();
        $this->createCallsPerFile[$file] = ($this->createCallsPerFile[$file] ?? 0) + 1;

        if ($this->createCallsPerFile[$file] > 1) {
            return [
                RuleErrorBuilder::message(self::MESSAGE)
                    ->identifier(self::RULE_IDENTIFIER)
                    ->build(),
            ];
        }

        return [];
    }

    private function isSchemaCreateCall(StaticCall $node, Scope $scope): bool
    {
        if (!$node->name instanceof Identifier || $node->name->toString() !== 'create') {
            return false;
        }

        if (!$node->class instanceof Name) {
            return false;
        }

        $resolved = $scope->resolveName($node->class);

        return $resolved === \Illuminate\Support\Facades\Schema::class
            || $resolved === 'Illuminate\\Database\\Schema\\Schema';
    }
}
