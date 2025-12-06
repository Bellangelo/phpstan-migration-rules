<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;

/**
 * @implements Rule<MethodCall>
 */
class EnforceCollationRule implements Rule
{
    private string $requiredCollation;

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
        if (!$node instanceof MethodCall) {
            return [];
        }

        $methodName = $node->name instanceof Node\Identifier ? $node->name->toString() : null;
        if ($methodName === null) {
            return [];
        }

        // Check if we're in a Phinx migration class (extends AbstractMigration)
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        if (!$classReflection->isSubclassOf('Phinx\Migration\AbstractMigration')) {
            return [];
        }

        // Handle 'table' method calls
        if ($methodName === 'table') {
            return $this->validateTableMethod($node, $scope);
        }

        return [];
    }

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
                )->line($node->getStartLine())->build(),
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
                )->line($node->getStartLine())->build(),
            ];
        }

        // Check if collation is set in the options array
        $hasCollation = false;
        $collationValue = null;

        foreach ($optionsArg->items as $item) {
            if ($item === null || !$item instanceof ArrayItem || $item->key === null) {
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
                )->line($node->getStartLine())->build(),
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
                )->line($node->getStartLine())->build(),
            ];
        }

        return [];
    }

    /**
     * Extract string value from a node, using PHPStan's type system to resolve constants
     */
    private function getStringValue(Node $node, Scope $scope): ?string
    {
        $type = $scope->getType($node);
        if ($type instanceof ConstantStringType) {
            return $type->getValue();
        }

        return null;
    }
}

