<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<StaticCall>
 */
final class ForbidMultipleTableCreationsRule implements Rule
{
    private const string RULE_IDENTIFIER = 'laravel.schema.multipleTableCreationsForbidden';

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
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        if (!$classReflection->isSubclassOf(\Illuminate\Database\Migrations\Migration::class)) {
            return [];
        }

        if (!$this->isSchemaCreateCall($node, $scope)) {
            return [];
        }

        $file = $scope->getFile();
        $this->createCallsPerFile[$file] = ($this->createCallsPerFile[$file] ?? 0) + 1;

        if ($this->createCallsPerFile[$file] > 1) {
            return [
                RuleErrorBuilder::message(
                    'Creating multiple tables in a single Laravel migration is forbidden. '
                    . 'Each migration should create exactly one table.'
                )
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
            || $resolved === 'Illuminate\\Database\\Schema\\Schema'; // Rare case
    }
}
