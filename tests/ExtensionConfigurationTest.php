<?php

declare(strict_types=1);

namespace PhpStanMigrationRules\Tests;

use PhpStanMigrationRules\Rules\Laravel\EnforceCollationRule as LaravelEnforceCollationRule;
use PhpStanMigrationRules\Rules\Laravel\ForbidAfterRule as LaravelForbidAfterRule;
use PhpStanMigrationRules\Rules\Laravel\ForbidMultipleTableCreationsRule as LaravelForbidMultipleTableCreationsRule;
use PhpStanMigrationRules\Rules\Phinx\EnforceCollationRule as PhinxEnforceCollationRule;
use PhpStanMigrationRules\Rules\Phinx\ForbidAfterRule as PhinxForbidAfterRule;
use PhpStanMigrationRules\Rules\Phinx\ForbidMultipleTableCreationsRule as PhinxForbidMultipleTableCreationsRule;
use PHPStan\DependencyInjection\Container;
use PHPStan\Rules\Rule;
use PHPStan\Testing\PHPStanTestCase;

final class ExtensionConfigurationTest extends PHPStanTestCase
{
    /**
     * @var mixed[]
     */
    private const ALL_RULE_CLASSES = [
        PhinxEnforceCollationRule::class,
        PhinxForbidAfterRule::class,
        PhinxForbidMultipleTableCreationsRule::class,
        LaravelEnforceCollationRule::class,
        LaravelForbidAfterRule::class,
        LaravelForbidMultipleTableCreationsRule::class,
    ];

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../extension.neon'];
    }

    public function testExtensionLoadsWithoutErrors(): void
    {
        $container = self::getContainer();

        self::assertInstanceOf(Container::class, $container);
    }

    public function testAllRulesAreRegisteredAsServices(): void
    {
        $container = self::getContainer();

        foreach (self::ALL_RULE_CLASSES as $ruleClass) {
            $services = $container->findServiceNamesByType($ruleClass);

            self::assertNotEmpty(
                $services,
                sprintf('Rule %s is not registered as a service.', $ruleClass),
            );
        }
    }

    public function testAllRulesAreTaggedWhenEnabled(): void
    {
        $container = self::getContainer();
        $taggedServices = $container->getServicesByTag('phpstan.rules.rule');

        $taggedClasses = [];
        foreach ($taggedServices as $service) {
            self::assertIsObject($service);
            $taggedClasses[] = get_class($service);
        }

        foreach (self::ALL_RULE_CLASSES as $ruleClass) {
            self::assertContains(
                $ruleClass,
                $taggedClasses,
                sprintf('Rule %s is not tagged with phpstan.rules.rule.', $ruleClass),
            );
        }
    }

    public function testAllTaggedRulesImplementRuleInterface(): void
    {
        $container = self::getContainer();
        $taggedServices = $container->getServicesByTag('phpstan.rules.rule');

        foreach ($taggedServices as $service) {
            self::assertIsObject($service);
            self::assertInstanceOf(
                Rule::class,
                $service,
                sprintf('%s does not implement Rule interface.', get_class($service)),
            );
        }
    }

    public function testDefaultParameterValues(): void
    {
        $container = self::getContainer();

        /** @var array{requiredCollation: string, phinx: array<string, bool>, laravel: array<string, bool>} $parameters */
        $parameters = $container->getParameter('migrationRules');

        self::assertSame('utf8', $parameters['requiredCollation']);

        self::assertTrue($parameters['phinx']['enforceCollation']);
        self::assertTrue($parameters['phinx']['forbidAfter']);
        self::assertTrue($parameters['phinx']['forbidMultipleTableCreations']);

        self::assertTrue($parameters['laravel']['enforceCollation']);
        self::assertTrue($parameters['laravel']['forbidAfter']);
        self::assertTrue($parameters['laravel']['forbidMultipleTableCreations']);
    }
}
