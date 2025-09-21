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

namespace Ixnode\PhpQualitySuite;

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
    private RectorParameters $rectorParameters;

    private RectorPaths $rectorPaths;

    private bool $debug = false;

    private const PATH_APP_KERNEL_DEV_DEBUG_CONTAINER = "var/cache/dev/App_KernelDevDebugContainer.xml";

    /**
     */
    public function __construct()
    {
        $this->rectorParameters = new RectorParameters();
        $this->rectorPaths = new RectorPaths();
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Returns the ready configured RectorConfigBuilder.
     *
     * @throws InvalidConfigurationException
     */
    public function getRectorConfigBuilder(): RectorConfigBuilderVendor
    {
        $rectorConfigBuilder = RectorConfig::configure();

        $this->addPaths($rectorConfigBuilder);
        $this->addPhpLevel($rectorConfigBuilder);
        $this->addPreparedSets($rectorConfigBuilder);
        $this->addSymfonySets($rectorConfigBuilder);

        return $rectorConfigBuilder;
    }

    /**
     * Add included and excluded paths.
     */
    private function addPaths(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        $paths = $this->getRectorPathsIncluded();
        $skip = $this->getRectorPathsExcluded();

        $rectorConfigBuilderVendor
            ->withPaths($paths)
            ->withSkip($skip)
        ;
    }

    /**
     * Add PHP level.
     *
     * @throws InvalidConfigurationException
     */
    private function addPhpLevel(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        $level = $this->rectorParameters->getLevel();

        /* Uses the composer.json PHP version: require.php */
        match (true) {
            is_null($level) => $rectorConfigBuilderVendor->withPhpSets(),
            default => $rectorConfigBuilderVendor->withPhpLevel($level),
        };
    }

    /**
     * Adds withPreparedSets.
     */
    private function addPreparedSets(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        $rectorConfigBuilderVendor->withPreparedSets(
            deadCode: $this->rectorParameters->getRule('deadCode'),
            codeQuality: $this->rectorParameters->getRule('codeQuality'),
            codingStyle: $this->rectorParameters->getRule('codingStyle'),
            typeDeclarations: $this->rectorParameters->getRule('typeDeclarations'),
            privatization: $this->rectorParameters->getRule('privatization'),
            naming: $this->rectorParameters->getRule('naming'),
            instanceOf: $this->rectorParameters->getRule('instanceOf'),
            earlyReturn: $this->rectorParameters->getRule('earlyReturn'),
            strictBooleans: $this->rectorParameters->getRule('strictBooleans'),
            carbon: $this->rectorParameters->getRule('carbon'),
            rectorPreset: $this->rectorParameters->getRule('rectorPreset'),
            phpunitCodeQuality: $this->rectorParameters->getRule('phpunitCodeQuality'),
            doctrineCodeQuality: $this->rectorParameters->getRule('doctrineCodeQuality'),
            symfonyCodeQuality: $this->rectorParameters->getRule('symfonyCodeQuality'),
            symfonyConfigs: $this->rectorParameters->getRule('symfonyConfigs'),
        );

        $deadCodeLevel = $this->rectorParameters->getRuleLevel('deadCode');
        if (is_int($deadCodeLevel)) {
            $rectorConfigBuilderVendor->withDeadCodeLevel($deadCodeLevel);
        }

        $codeQualityLevel = $this->rectorParameters->getRuleLevel('codeQuality');
        if (is_int($codeQualityLevel)) {
            $rectorConfigBuilderVendor->withCodeQualityLevel($codeQualityLevel);
        }

        $codingStyleLevel = $this->rectorParameters->getRuleLevel('codingStyle');
        if (is_int($codingStyleLevel)) {
            $rectorConfigBuilderVendor->withCodingStyleLevel($codingStyleLevel);
        }

        $typeCoverageLevel = $this->rectorParameters->getRuleLevel('typeDeclarations');
        if (is_int($typeCoverageLevel)) {
            $rectorConfigBuilderVendor->withTypeCoverageLevel($typeCoverageLevel);
        }
    }

    /**
     * Adds symfony sets.
     *
     * @throws InvalidConfigurationException
     */
    private function addSymfonySets(RectorConfigBuilderVendor $rectorConfigBuilderVendor): void
    {
        $withSymfony = $this->rectorParameters->getWithSymfony();

        if (is_null($withSymfony)) {
            return;
        }

        if (file_exists(self::PATH_APP_KERNEL_DEV_DEBUG_CONTAINER)) {
            $rectorConfigBuilderVendor
                ->withSymfonyContainerXml(self::PATH_APP_KERNEL_DEV_DEBUG_CONTAINER)
            ;
        }

        $sets = [
            RectorParameters::ALLOWED_SYMFONY_VERSIONS[$withSymfony]
        ];

        if ($this->rectorParameters->getWithSymfonyCodeQuality()) {
            $sets[] = SymfonySetList::SYMFONY_CODE_QUALITY;
        }

        if ($this->rectorParameters->getWithSymfonyConstructorInjection()) {
            $sets[] = SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION;
        }

        $rectorConfigBuilderVendor
            ->withSets($sets);
    }


    /**
     * Prints the current rector setup.
     *
     * @throws InvalidConfigurationException
     */
    public function printSetup(): void
    {
        $printed = (int)getenv('RECTOR_OVERVIEW_PRINTED');

        if ($printed >= ($this->debug ? 2 : 1)) {

            if ($this->debug) {
                exit();
            }

            return;
        }

        $level = $this->rectorParameters->getLevel();

        $activeRules = array_keys(
            array_filter(
                $this->rectorParameters->getRules(),
                static fn(bool $enabled): bool => $enabled
            )
        );

        $phpVersion = $this->formatPhpVersionId(ComposerJsonPhpVersionResolver::resolveFromCwdOrFail());
        $includedPaths = $this->rectorParameters->getIncludedPaths();
        $withSymfony = $this->rectorParameters->getWithSymfony();
        $withSymfonyPrint = $withSymfony !== null && $withSymfony !== '' && $withSymfony !== '0' ? $withSymfony.'.x' : 'N/A';
        $withSymfonyCodeQuality = $this->rectorParameters->getWithSymfonyCodeQuality();
        $withSymfonyConstructorInjection = $this->rectorParameters->getWithSymfonyConstructorInjection();

        echo PHP_EOL;
        echo "Rector Overview".PHP_EOL;
        echo "---------------".PHP_EOL;
        echo sprintf("Level:                              %s", $level ?? 'N/A').PHP_EOL;
        echo sprintf("Include paths:                      %s", $includedPaths === [] ? 'all' : implode(', ', $this->rectorParameters->getIncludedPaths())).PHP_EOL;
        echo sprintf("Rules:                              %s", $activeRules === [] ? 'N/A' : implode(', ', $activeRules)).PHP_EOL;
        echo sprintf("With php version:                   %s", $phpVersion).PHP_EOL;
        echo sprintf("With symfony version:               %s", $withSymfonyPrint).PHP_EOL;
        echo sprintf("With symfony code quality:          %s", $withSymfonyCodeQuality ? 'yes' : 'no').PHP_EOL;
        echo sprintf("With symfony constructor injection: %s", $withSymfonyConstructorInjection ? 'yes' : 'no').PHP_EOL;

        if ($this->rectorParameters->getDetails()) {
            echo PHP_EOL;

            echo "Rule Details".PHP_EOL;
            echo "------------".PHP_EOL;
            echo sprintf("deadCode:            %s", $this->getRuleState('deadCode')).PHP_EOL;
            echo sprintf("codeQuality:         %s", $this->getRuleState('codeQuality')).PHP_EOL;
            echo sprintf("codingStyle:         %s", $this->getRuleState('codingStyle')).PHP_EOL;
            echo sprintf("typeDeclarations:    %s", $this->getRuleState('typeDeclarations')).PHP_EOL;
            echo sprintf("privatization:       %s", $this->getRuleState('privatization')).PHP_EOL;
            echo sprintf("naming:              %s", $this->getRuleState('naming')).PHP_EOL;
            echo sprintf("instanceOf:          %s", $this->getRuleState('instanceOf')).PHP_EOL;
            echo sprintf("earlyReturn:         %s", $this->getRuleState('earlyReturn')).PHP_EOL;
            echo sprintf("strictBooleans:      %s", $this->getRuleState('strictBooleans')).PHP_EOL;
            echo sprintf("carbon:              %s", $this->getRuleState('carbon')).PHP_EOL;
            echo sprintf("rectorPreset:        %s", $this->getRuleState('rectorPreset')).PHP_EOL;
            echo sprintf("phpunitCodeQuality:  %s", $this->getRuleState('phpunitCodeQuality')).PHP_EOL;
            echo sprintf("doctrineCodeQuality: %s", $this->getRuleState('doctrineCodeQuality')).PHP_EOL;
            echo sprintf("symfonyCodeQuality:  %s", $this->getRuleState('symfonyCodeQuality')).PHP_EOL;
            echo sprintf("symfonyConfigs:      %s", $this->getRuleState('symfonyConfigs')).PHP_EOL;
        }

        echo PHP_EOL;
        echo PHP_EOL;

        ++$printed;

        putenv('RECTOR_OVERVIEW_PRINTED='.$printed);
    }

    /**
     * Returns the printable rule property (state).
     */
    private function getRuleState(string $key): string
    {
        $valueOrLevel = $this->rectorParameters->getRuleOrRuleLevel($key);

        if (is_bool($valueOrLevel)) {
            return $valueOrLevel ? 'Active' : 'Not active';
        }

        return 'Level: '.$valueOrLevel;
    }

    /**
     * Formats the rector php version into a readable format.
     */
    private function formatPhpVersionId(int $id): string
    {
        $major = intdiv($id, 10000);
        $minor = intdiv($id % 10000, 100);
        $patch = 'x'; // $id % 100;

        return sprintf('%d.%d.%s', $major, $minor, $patch);
    }

    /**
     * Returns the ready to use included analyzation paths.
     */
    private function getRectorPathsIncluded(): array
    {
        $paths = $this->rectorParameters->getIncludedPaths();

        $selected = $paths === [] ? $this->rectorPaths->getAll() : $this->rectorPaths->getOnly(...$paths);

        return array_map(
            static fn(string $path): string => $path,
            $selected
        );
    }

    /**
     * Returns the ready to use excluded analyzation paths.
     */
    private function getRectorPathsExcluded(): array
    {
        return array_map(
            static fn(string $path): string => $path,
            $this->rectorParameters->getPathsExcluded()
        );
    }
}
