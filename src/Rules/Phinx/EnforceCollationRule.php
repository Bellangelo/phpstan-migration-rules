<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Rules\IdentifierRuleError;

/**
 * @extends PhinxRule<MethodCall>
 */
class EnforceCollationRule extends PhinxRule
{
    private string $requiredCollation = 'utf8';
    public function __construct(string $requiredCollation = 'utf8')
    {
        $this->requiredCollation = $requiredCollation;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isPhinxMigration($scope)) {
            return [];
        }

        $methodName = $node->name instanceof Node\Identifier ? $node->name->toString() : null;
        if ($methodName === null) {
            return [];
        }

        // Handle 'table' method calls
        if ($methodName === 'table') {
            return $this->validateTableMethod($node, $scope);
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function validateTableMethod(MethodCall $node, Scope $scope): array
    {
        // Check if the table method has the required collation option
        $args = $node->getArgs();
        if (count($args) < 2) {
            // Only table name provided, no options
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Phinx table() method must specify collation. Expected collation: "%s"',
                        $this->requiredCollation
                    )
                )->line($node->getStartLine())
                ->identifier('phinx.table.missing.collation')
                ->build(),
            ];
        }

        $optionsArg = $args[1]->value;
        if (!$optionsArg instanceof Array_) {
            // Options not provided as an array
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Phinx table() method options must be an array with collation set to "%s"',
                        $this->requiredCollation
                    )
                )->line($node->getStartLine())
                ->identifier('phinx.table.options.not.array')
                ->build(),
            ];
        }

        // Check if collation is set in the options array
        $hasCollation = false;
        $collationValue = null;

        foreach ($optionsArg->items as $item) {
            if ($item->key === null) {
                continue;
            }

            // Check if this is the 'collation' key
            $keyValue = $this->getStringValue($item->key, $scope);
            if ($keyValue === 'collation') {
                $hasCollation = true;
                $collationValue = $this->getStringValue($item->value, $scope);
                break; // Found the collation key, no need to continue
            }
        }

        if (!$hasCollation) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Phinx table() method must specify collation option. Expected: "%s"',
                        $this->requiredCollation
                    )
                )->line($node->getStartLine())
                ->identifier('phinx.table.missing.collation')
                ->build(),
            ];
        }

        if ($collationValue !== null && $collationValue !== $this->requiredCollation) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Phinx table() method collation must be "%s", got "%s"',
                        $this->requiredCollation,
                        $collationValue
                    )
                )->line($node->getStartLine())
                ->identifier('phinx.table.wrong.collation')
                ->build(),
            ];
        }

        return [];
    }

    /**
     * Extract string value from a node, using PHPStan's type system to resolve constants
     */
    private function getStringValue(Expr $node, Scope $scope): ?string
    {
        $type = $scope->getType($node);
        
        if (count($type->getConstantStrings()) === 0) {
            return null;
        }
        
        return $type->getConstantStrings()[0]->getValue();
    }
}
