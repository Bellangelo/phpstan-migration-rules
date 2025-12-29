<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids creating more than one table in a single Phinx migration.
 *
 * @extends PhinxRule<MethodCall>
 */
final class ForbidMultipleTableCreationsRule extends PhinxRule
{
    private const RULE_IDENTIFIER = 'phinx.multipleTableCreationsForbidden';

    /**
     * @var array<string, int>
     */
    private array $createCallsPerFile = [];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        if (!$this->isCreateCall($node)) {
            return [];
        }

        $file = $scope->getFile();
        $this->createCallsPerFile[$file] = ($this->createCallsPerFile[$file] ?? 0) + 1;

        if ($this->createCallsPerFile[$file] > 1) {
            return [
                RuleErrorBuilder::message(
                    'Creating multiple tables in a single Phinx migration is forbidden. '
                    . 'Each migration should create exactly one table.'
                )
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
            ];
        }

        return [];
    }

    private function isCreateCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && $node->name->toString() === 'create';
    }
}
