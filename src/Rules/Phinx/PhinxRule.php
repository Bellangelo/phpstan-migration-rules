<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Rules\Phinx;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @template TNode of Node
 * @implements Rule<TNode>
 */
abstract class PhinxRule implements Rule
{
    public function isPhinxMigration(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        if (!$classReflection->isSubclassOf(\Phinx\Migration\AbstractMigration::class)) {
            return false;
        }

        return true;
    }
}
