<?php

declare(strict_types=1);

namespace Ixnode\PhpQualitySuite\Rector;

use Rector\Bridge\SetRectorsResolver;
use Rector\Configuration\PhpLevelSetResolver;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use ReflectionClass;
use ReflectionException;

/**
 * Class RectorConfigDebugger
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-28)
 * @since 1.0.0 (2025-09-28) First version
 */
class RectorConfigDebugger
{
    /**
     */
    public function __construct(private RectorConfigBuilder $rectorConfigBuilder, private int|null|false $level = null)
    {
    }

    /**
     * Returns all Rector rules configured in the loaded sets.
     *
     * @return string[]
     * @throws ReflectionException
     * @throws InvalidConfigurationException
     */
    public function getRules(): array
    {
        return match (true) {
            $this->level === false => $this->getRulesViaInclude(),
            $this->level === null => $this->getRulesViaSets(),
            default => $this->getRulesViaLevel($this->level),
        };
    }

    /**
     * Returns all rules from withRules method (no level, no set).
     *
     * @return class-string[]
     * @throws ReflectionException
     */
    private function getRulesViaInclude(): array
    {
        $rules = $this->getPrivateProperty($this->rectorConfigBuilder, 'rules');

        return array_values(array_filter(
            $rules,
            static fn ($rule): bool => is_string($rule) && class_exists($rule)
        ));
    }

    /**
     * Returns all rules from withPhpSets method (without given level).
     *
     * @return class-string[]
     * @throws ReflectionException
     */
    private function getRulesViaSets(): array
    {
        $rectorConfig = new RectorConfig();

        $sets = $this->getRectorConfigBuilderSets();

        foreach ($sets as $setFile) {
            $closure = require $setFile;
            $closure($rectorConfig);
        }

        $rectorClasses = $rectorConfig->getRectorClasses();

        return array_values(array_filter(
            $rectorClasses,
            static fn ($rule): bool => is_string($rule) && class_exists($rule)
        ));
    }

    /**
     * Returns all rules from withPhpLevel method (with given level).
     *
     * @return class-string[]
     * @throws InvalidConfigurationException
     */
    private function getRulesViaLevel(int $level): array
    {
        $phpVersion = ComposerJsonPhpVersionResolver::resolveFromCwdOrFail();
        $setRectorsResolver = new SetRectorsResolver();
        $setFilePaths = PhpLevelSetResolver::resolveFromPhpVersion($phpVersion);
        $rectorRulesWithConfiguration = $setRectorsResolver->resolveFromFilePathsIncludingConfiguration($setFilePaths);

        $rectorRulesWithConfigurationSliced = array_slice($rectorRulesWithConfiguration, 0, $level + 1, true);

        return array_values(array_filter(
            $rectorRulesWithConfigurationSliced,
            static fn ($rule): bool => is_string($rule) && class_exists($rule)
        ));
    }

    /**
     * Returns the RectorConfigBuilder sets.
     *
     * @throws ReflectionException
     */
    private function getRectorConfigBuilderSets(): array
    {
//        /* Only PHP sets. */
//        $projectPhpVersion = ComposerJsonPhpVersionResolver::resolveFromCwdOrFail();
//        return PhpLevelSetResolver::resolveFromPhpVersion($projectPhpVersion);

        /* Return all assigned sets (from withPhpSets, withPreparedSets, withImportNames, etc.). */
        return $this->getPrivateProperty($this->rectorConfigBuilder, 'sets');
    }

    /**
     * Helper method: Access a private property of an object via reflection.
     *
     * @throws ReflectionException
     */
    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}
