<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @extends PhinxRule<MethodCall>
 */
final class EnforceCollationRule extends PhinxRule
{
    /**
     * @readonly
     */
    private string $requiredCollation;
    // Same identifier as Laravel
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'phinx.schema.requiredCollation';

    /**
     * @var string
     */
    private const MESSAGE_MISSING =
        'Required: table collation must be "%s". '
        . 'Why: prevents environment-dependent defaults and keeps schema consistent. '
        . 'Fix: set the table collation explicitly in the migration.';

    /**
     * @var string
     */
    private const MESSAGE_WRONG =
        'Required: table collation must be "%s". Found: "%s". '
        . 'Why: prevents environment-dependent defaults and keeps schema consistent. '
        . 'Fix: set the table collation explicitly in the migration.';

    public function __construct(string $requiredCollation)
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
        if ($methodName !== 'table') {
            return [];
        }

        return $this->validateTableMethod($node, $scope);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function validateTableMethod(MethodCall $node, Scope $scope): array
    {
        $args = $node->getArgs();

        // Missing options argument
        if (!isset($args[1])) {
            return $this->missing($node);
        }

        $optionsArg = $args[1]->value;
        if (!$optionsArg instanceof Array_) {
            // options provided but not as array -> effectively missing for our policy
            return $this->missing($node);
        }

        $collationValue = null;

        foreach ($optionsArg->items as $item) {
            if ($item->key === null) {
                continue;
            }

            $keyValue = $this->getStringValue($item->key, $scope);
            if ($keyValue !== 'collation') {
                continue;
            }

            $collationValue = $this->getStringValue($item->value, $scope);
            break;
        }

        if ($collationValue === null) {
            return $this->missing($node);
        }

        if ($collationValue !== $this->requiredCollation) {
            return $this->wrong($node, $collationValue);
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function missing(MethodCall $node): array
    {
        return [
            RuleErrorBuilder::message(sprintf(self::MESSAGE_MISSING, $this->requiredCollation))
                ->line($node->getStartLine())
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function wrong(MethodCall $node, string $found): array
    {
        return [
            RuleErrorBuilder::message(sprintf(self::MESSAGE_WRONG, $this->requiredCollation, $found))
                ->line($node->getStartLine())
                ->identifier(self::RULE_IDENTIFIER)
                ->build(),
        ];
    }

    private function getStringValue(Expr $node, Scope $scope): ?string
    {
        $type = $scope->getType($node);
        $constants = $type->getConstantStrings();

        if ($constants === []) {
            return null;
        }

        return $constants[0]->getValue();
    }
}
