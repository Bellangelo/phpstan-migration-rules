<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<StaticCall>
 */
final class EnforceCollationRule implements Rule
{
    /**
     * @readonly
     */
    private string $requiredCollation;
    /**
     * @var string
     */
    private const RULE_IDENTIFIER = 'laravel.schema.requiredCollation';

    public function __construct(string $requiredCollation)
    {
        $this->requiredCollation = $requiredCollation;
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Only apply inside Laravel migration classes.
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        if (!$classReflection->isSubclassOf(\Illuminate\Database\Migrations\Migration::class)) {
            return [];
        }

        $callName = $this->getStaticCallName($node);
        if ($callName !== 'create' && $callName !== 'table') {
            return [];
        }

        if (!$node->class instanceof Name) {
            return [];
        }

        $resolved = $scope->resolveName($node->class);
        if (
            $resolved !== \Illuminate\Support\Facades\Schema::class
            && $resolved !== 'Illuminate\\Database\\Schema\\Schema'
        ) {
            return [];
        }

        $closure = $this->extractBlueprintCallback($node);
        if ($closure === null) {
            return [];
        }

        $tableVarName = $this->extractBlueprintParamName($closure);
        if ($tableVarName === null) {
            return [];
        }

        $collation = $this->findCollationInClosure($closure, $tableVarName);

        if ($collation !== $this->requiredCollation) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Laravel migrations must set table collation to "%s" in Schema::%s().',
                    $this->requiredCollation,
                    $callName
                ))
                    ->identifier(self::RULE_IDENTIFIER)
                    ->build(),
            ];
        }

        return [];
    }

    private function getStaticCallName(StaticCall $node): ?string
    {
        return $node->name instanceof Identifier ? $node->name->toString() : null;
    }

    private function extractBlueprintCallback(StaticCall $node): ?Closure
    {
        if (!isset($node->args[1]) || !$node->args[1] instanceof Arg) {
            return null;
        }

        $value = $node->args[1]->value;
        return $value instanceof Closure ? $value : null;
    }

    private function extractBlueprintParamName(Closure $closure): ?string
    {
        if (!isset($closure->params[0])) {
            return null;
        }

        $var = $closure->params[0]->var;
        if (!$var instanceof Variable) {
            return null;
        }

        return is_string($var->name) ? $var->name : null;
    }

    private function findCollationInClosure(Closure $closure, string $tableVarName): ?string
    {
        $stmts = $closure->stmts;

        foreach ($stmts as $stmt) {
            $found = $this->scanNodeForCollation($stmt, $tableVarName);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function scanNodeForCollation(Node $node, string $tableVarName): ?string
    {
        // Do not walk into nested functions; they can capture $table and create false positives.
        if ($node instanceof Closure || $node instanceof ArrowFunction) {
            return null;
        }

        // $table->collation('utf8mb4')
        if (
            $node instanceof MethodCall
            && $node->name instanceof Identifier
            && $node->name->toString() === 'collation'
            && $this->isTableVar($node->var, $tableVarName)
            && isset($node->args[0])
            && $node->args[0] instanceof Arg
            && $node->args[0]->value instanceof String_
        ) {
            return $node->args[0]->value->value;
        }

        // $table->collation = 'utf8mb4'
        if (
            $node instanceof Assign
            && $node->var instanceof PropertyFetch
            && $node->var->name instanceof Identifier
            && $node->var->name->toString() === 'collation'
            && $this->isTableVar($node->var->var, $tableVarName)
            && $node->expr instanceof String_
        ) {
            return $node->expr->value;
        }

        // Recurse into child nodes
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;

            if ($subNode instanceof Node) {
                $found = $this->scanNodeForCollation($subNode, $tableVarName);
                if ($found !== null) {
                    return $found;
                }
                continue;
            }

            if (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node) {
                        $found = $this->scanNodeForCollation($item, $tableVarName);
                        if ($found !== null) {
                            return $found;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function isTableVar(Node $node, string $tableVarName): bool
    {
        if (!$node instanceof Variable) {
            return false;
        }

        return is_string($node->name) && $node->name === $tableVarName;
    }
}
