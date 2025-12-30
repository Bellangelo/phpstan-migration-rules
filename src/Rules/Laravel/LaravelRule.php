<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Laravel;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @template TNode of Node
 * @implements Rule<TNode>
 */
abstract class LaravelRule implements Rule
{
    public function isLaravelMigration(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        if (!$classReflection->isSubclassOf(\Illuminate\Database\Migrations\Migration::class)) {
            return false;
        }

        return true;
    }
}
