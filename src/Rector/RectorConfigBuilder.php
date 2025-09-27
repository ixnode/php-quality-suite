<?php

/*
 * This file is part of the ixnode/php-quality-suite project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ixnode\PhpQualitySuite\Rector;

use Ixnode\PhpQualitySuite\Parameters;
use Ixnode\PhpQualitySuite\Paths;
use Ixnode\PhpQualitySuite\Utils\PhpVersion;
use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder as RectorConfigBuilderVendor;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use Rector\Symfony\Set\SymfonySetList;

/**
 * Class RectorConfigBuilder
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 */
final class RectorConfigBuilder
{
    private Parameters $parameters;

    private Paths $paths;

    /** @var string[] $pathsIncluded */
    private array $pathsIncluded = [];

    /** @var string[]|null $pathsExcluded */
    private array|null $pathsExcluded = null;

    /** @var string[]|null $rulesIncluded */
    private array|null $rulesIncluded = null;

    /** @var string[]|null $rulesExcluded */
    private array|null $rulesExcluded = null;

    private int|false|null $phpVersion = false;

    private const PATH_APP_KERNEL_DEV_DEBUG_CONTAINER = "var/cache/dev/App_KernelDevDebugContainer.xml";

    /**
     */
    public function __construct()
    {
        $this->parameters = new Parameters();
        $this->paths = new Paths($this->parameters);
    }

    /**
     * Returns the parameters.
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * Returns the paths.
     */
    public function getPaths(): Paths
    {
        return $this->paths;
    }

    /**
     * Returns the ready configured RectorConfigBuilder.
     *
     * @throws InvalidConfigurationException
     */
    public function getRectorConfigBuilder(): RectorConfigBuilderVendor
    {
        $rectorConfigBuilder = RectorConfig::configure();

        $this->addPaths();
        $this->addRules();
        $this->addPhpLevel();

        $this->assignConfiguration($rectorConfigBuilder);
        $this->assignPreparedSets($rectorConfigBuilder);
        $this->assignImportNaming($rectorConfigBuilder);
        $this->assignSymfonySets($rectorConfigBuilder);

        return $rectorConfigBuilder;
    }

    /**
     * Add included and excluded paths.
     */
    private function addPaths(): void
    {
        $this->pathsIncluded = $this->parameters->getPathsIncludedFiltered();
        $this->pathsExcluded = $this->parameters->getPathsExcludedFiltered();
    }

    /**
     * Add rules.
     *
     * @throws InvalidConfigurationException
     */
    private function addRules(): void
    {
        /* Either add included rules or excluded rules. */
        if (!$this->parameters->hasRulesIncludedFiltered()) {
            $phpVersion = new PhpVersion(ComposerJsonPhpVersionResolver::resolveFromCwdOrFail());

            $this->rulesExcluded = $this->parameters->getRulesExcludedFiltered($phpVersion->getNumber());

            return;
        }

        $this->rulesIncluded = $this->parameters->getRulesIncludedFiltered();
    }

    /**
     * Add PHP level.
     */
    private function addPhpLevel(): void
    {
        if ($this->parameters->hasRulesIncludedFiltered()) {
            $this->phpVersion = false;
            return;
        }

        $this->phpVersion = $this->parameters->getLevel();
    }

    /**
     * Assign configuration.
     *
     * @throws InvalidConfigurationException
     */
    private function assignConfiguration(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        $rectorConfigBuilderVendor->withPaths($this->pathsIncluded);
        $rectorConfigBuilderVendor->withSkip([...($this->pathsExcluded ?? []), ...($this->rulesExcluded ?? [])]);

        if (!is_null($this->rulesIncluded)) {
            $rectorConfigBuilderVendor->withRules($this->rulesIncluded);
        }

        if ($this->phpVersion !== false) {
            match (true) {
                is_null($this->phpVersion) => $rectorConfigBuilderVendor->withPhpSets(),
                default => $rectorConfigBuilderVendor->withPhpLevel($this->phpVersion),
            };
        }
    }

    /**
     * Assign withPreparedSets.
     */
    private function assignPreparedSets(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        if ($this->parameters->hasRulesIncludedFiltered()) {
            return;
        }

        $rectorConfigBuilderVendor->withPreparedSets(
            deadCode: $this->parameters->getPreparedSet('deadCode'),
            codeQuality: $this->parameters->getPreparedSet('codeQuality'),
            codingStyle: $this->parameters->getPreparedSet('codingStyle'),
            typeDeclarations: $this->parameters->getPreparedSet('typeDeclarations'),
            privatization: $this->parameters->getPreparedSet('privatization'),
            naming: $this->parameters->getPreparedSet('naming'),
            instanceOf: $this->parameters->getPreparedSet('instanceOf'),
            earlyReturn: $this->parameters->getPreparedSet('earlyReturn'),
            strictBooleans: $this->parameters->getPreparedSet('strictBooleans'),
            carbon: $this->parameters->getPreparedSet('carbon'),
            rectorPreset: $this->parameters->getPreparedSet('rectorPreset'),
            phpunitCodeQuality: $this->parameters->getPreparedSet('phpunitCodeQuality'),
            doctrineCodeQuality: $this->parameters->getPreparedSet('doctrineCodeQuality'),
            symfonyCodeQuality: $this->parameters->getPreparedSet('symfonyCodeQuality'),
            symfonyConfigs: $this->parameters->getPreparedSet('symfonyConfigs'),
        );

        $deadCodeLevel = $this->parameters->getPreparedSetLevel('deadCode');
        if (is_int($deadCodeLevel)) {
            $rectorConfigBuilderVendor->withDeadCodeLevel($deadCodeLevel);
        }

        $codeQualityLevel = $this->parameters->getPreparedSetLevel('codeQuality');
        if (is_int($codeQualityLevel)) {
            $rectorConfigBuilderVendor->withCodeQualityLevel($codeQualityLevel);
        }

        $codingStyleLevel = $this->parameters->getPreparedSetLevel('codingStyle');
        if (is_int($codingStyleLevel)) {
            $rectorConfigBuilderVendor->withCodingStyleLevel($codingStyleLevel);
        }

        $typeCoverageLevel = $this->parameters->getPreparedSetLevel('typeDeclarations');
        if (is_int($typeCoverageLevel)) {
            $rectorConfigBuilderVendor->withTypeCoverageLevel($typeCoverageLevel);
        }
    }

    /**
     * Assign withImportNames.
     */
    private function assignImportNaming(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        if ($this->parameters->hasRulesIncludedFiltered()) {
            return;
        }

        $rectorConfigBuilderVendor->withImportNames(
            importNames: $this->parameters->getImportName('importNames'),
            importDocBlockNames: $this->parameters->getImportName('importDocBlockNames'),
            importShortClasses: $this->parameters->getImportName('importShortClasses'),
            removeUnusedImports: $this->parameters->getImportName('removeUnusedImports')
        );
    }

    /**
     * Assign symfony sets.
     *
     * @throws InvalidConfigurationException
     */
    private function assignSymfonySets(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        if ($this->parameters->hasRulesIncludedFiltered()) {
            return;
        }

        $withSymfony = $this->parameters->getWithSymfony();

        if (is_null($withSymfony)) {
            return;
        }

        if (file_exists(self::PATH_APP_KERNEL_DEV_DEBUG_CONTAINER)) {
            $rectorConfigBuilderVendor
                ->withSymfonyContainerXml(self::PATH_APP_KERNEL_DEV_DEBUG_CONTAINER)
            ;
        }

        $sets = [
            Parameters::ALLOWED_SYMFONY_VERSIONS[$withSymfony]
        ];

        if ($this->parameters->getWithSymfonyCodeQuality()) {
            $sets[] = SymfonySetList::SYMFONY_CODE_QUALITY;
        }

        if ($this->parameters->getWithSymfonyConstructorInjection()) {
            $sets[] = SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION;
        }

        $rectorConfigBuilderVendor
            ->withSets($sets);
    }
}
