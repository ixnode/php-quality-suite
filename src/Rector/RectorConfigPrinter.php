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
use Ixnode\PhpQualitySuite\Utils\PhpVersion;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use ReflectionException;

/**
 * Class RectorConfigPrinter
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-27)
 * @since 1.0.0 (2025-09-27) First version
 */
final class RectorConfigPrinter
{
    private Parameters $parameters;

    private bool $debug = false;

    public const LENGTH_SEPARATOR = 50;

    /**
     */
    public function __construct(private RectorConfigBuilder $rectorConfigBuilder)
    {
        $this->parameters = $rectorConfigBuilder->getParameters();
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Prints the current rector setup.
     *
     * @throws InvalidConfigurationException
     */
    public function print(): void
    {
        $printed = (int) getenv('RECTOR_OVERVIEW_PRINTED');

        if ($printed >= ($this->debug ? 2 : 1)) {

            if ($this->debug) {
                exit();
            }

            return;
        }

        $this->printMain();
        $this->printPreparedSets();
        $this->printImportNames();
        $this->printRules();

        echo PHP_EOL;
        echo PHP_EOL;

        ++$printed;

        putenv('RECTOR_OVERVIEW_PRINTED='.$printed);
    }

    /**
     * Print the main information.
     *
     * @throws InvalidConfigurationException
     */
    private function printMain(): void
    {
        /* A */
        $phpVersionRector = new PhpVersion(ComposerJsonPhpVersionResolver::resolveFromCwdOrFail());
        $phpVersionRunning = new PhpVersion(phpversion());

        /* B */
        $includedPaths = $this->parameters->getInclude();
        $level = $this->parameters->getLevel();
        $activePreparedSets = array_keys(
            array_filter(
                $this->parameters->getPreparedSets(),
                static fn(bool $enabled): bool => $enabled
            )
        );
        $includedRules = $this->parameters->getRules();

        /* C */
        $withSymfony = $this->parameters->getWithSymfony();
        $withSymfonyPrint = $withSymfony !== null && $withSymfony !== '' && $withSymfony !== '0' ? $withSymfony.'.x' : 'N/A';
        $withSymfonyCodeQuality = $this->parameters->getWithSymfonyCodeQuality();
        $withSymfonyConstructorInjection = $this->parameters->getWithSymfonyConstructorInjection();

        /* D */
        $isDetails = $this->parameters->isDetails();
        $isDryRun = $this->parameters->isDryRun();

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "I) Rector Overview".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;

        /* A */
        echo sprintf('Running PHP version:                %s (%.1f)', $phpVersionRunning->getString(), $phpVersionRunning->getNumber()).PHP_EOL;
        echo sprintf('Rector analyse PHP version:         %s (%.1f)', $phpVersionRector->getString('%d.%d.x'), $phpVersionRector->getNumber()).PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* B */
        echo sprintf("Included paths:                     %s", $includedPaths === [] ? 'all' : implode(', ', $includedPaths)).PHP_EOL;
        echo sprintf("Level:                              %s", $level ?? 'max').PHP_EOL;
        echo sprintf("Sets:                               %s", $activePreparedSets === [] ? 'N/A' : implode(', ', $activePreparedSets)).PHP_EOL;
        echo sprintf("Included rules:                     %s", $includedRules === [] ? 'N/A' : implode(', ', $includedRules)).PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* C */
        echo sprintf("With symfony version:               %s", $withSymfonyPrint).PHP_EOL;
        echo sprintf("With symfony code quality:          %s", $withSymfonyCodeQuality ? 'yes' : 'no').PHP_EOL;
        echo sprintf("With symfony constructor injection: %s", $withSymfonyConstructorInjection ? 'yes' : 'no').PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* D */
        echo sprintf("Show details:                       %s", $isDetails ? 'Yes' : 'No').PHP_EOL;
        echo sprintf("Dry run mode:                       %s", $isDryRun ? 'Yes' : 'No').PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }

    /**
     * Print detailed prepared set information.
     */
    private function printPreparedSets(): void
    {
        if (!$this->parameters->isDetails()) {
            return;
        }

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "II) Prepared Set Details".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo sprintf("deadCode:              %s", $this->getPreparedSetState('deadCode')).PHP_EOL;
        echo sprintf("codeQuality:           %s", $this->getPreparedSetState('codeQuality')).PHP_EOL;
        echo sprintf("codingStyle:           %s", $this->getPreparedSetState('codingStyle')).PHP_EOL;
        echo sprintf("typeDeclarations:      %s", $this->getPreparedSetState('typeDeclarations')).PHP_EOL;
        echo sprintf("privatization:         %s", $this->getPreparedSetState('privatization')).PHP_EOL;
        echo sprintf("naming:                %s", $this->getPreparedSetState('naming')).PHP_EOL;
        echo sprintf("instanceOf:            %s", $this->getPreparedSetState('instanceOf')).PHP_EOL;
        echo sprintf("earlyReturn:           %s", $this->getPreparedSetState('earlyReturn')).PHP_EOL;
        echo sprintf("strictBooleans:        %s", $this->getPreparedSetState('strictBooleans')).PHP_EOL;
        echo sprintf("carbon:                %s", $this->getPreparedSetState('carbon')).PHP_EOL;
        echo sprintf("rectorPreset:          %s", $this->getPreparedSetState('rectorPreset')).PHP_EOL;
        echo sprintf("phpunitCodeQuality:    %s", $this->getPreparedSetState('phpunitCodeQuality')).PHP_EOL;
        echo sprintf("doctrineCodeQuality:   %s", $this->getPreparedSetState('doctrineCodeQuality')).PHP_EOL;
        echo sprintf("symfonyCodeQuality:    %s", $this->getPreparedSetState('symfonyCodeQuality')).PHP_EOL;
        echo sprintf("symfonyConfigs:        %s", $this->getPreparedSetState('symfonyConfigs')).PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }

    /**
     * Print detailed import name information.
     */
    private function printImportNames(): void
    {
        if (!$this->parameters->isDetails()) {
            return;
        }

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "III) Import Names Details".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo sprintf("importNames:           %s", $this->getImportNameState('importNames')).PHP_EOL;
        echo sprintf("importDocBlockNames:   %s", $this->getImportNameState('importDocBlockNames')).PHP_EOL;
        echo sprintf("importShortClasses:    %s", $this->getImportNameState('importShortClasses')).PHP_EOL;
        echo sprintf("removeUnusedImports:   %s", $this->getImportNameState('removeUnusedImports')).PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }

    /**
     * Print detailed rule information.
     *
     * @throws InvalidConfigurationException
     * @throws ReflectionException
     */
    private function printRules(): void
    {
        if (!$this->parameters->isDetails()) {
            return;
        }

        $rectorConfigDebugger = new RectorConfigDebugger(
            rectorConfigBuilder: $this->rectorConfigBuilder->getRectorConfigBuilder(),
            level: $this->rectorConfigBuilder->getLevel(),
        );

        $rules = $rectorConfigDebugger->getRules();

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "IV) Applied Rule Details".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;

        echo PHP_EOL;
        switch (true) {
            case count($rules) <= 0:
                echo "No rule applied.".PHP_EOL;
                break;

            default:
                $width = max(array_map('strlen', $rules));
                echo "┌───────┬─". str_repeat("─", $width)."─┐".PHP_EOL;
                echo "│ Level │ ". str_pad("Class", $width)." │".PHP_EOL;
                echo "│───────┼─". str_repeat("─", $width)."─┤".PHP_EOL;
                foreach ($rules as $level => $rule) {
                    echo sprintf("│ %5d │ %-{$width}s │", $level, $rule).PHP_EOL;
                }
                echo "└───────┴─". str_repeat("─", $width)."─┘".PHP_EOL;
                break;
        }
        echo PHP_EOL;

        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }

    /**
     * Returns the printable prepared set property (state).
     */
    private function getPreparedSetState(string $key): string
    {
        $valueOrLevel = $this->parameters->getPreparedSetOrPreparedSetLevel($key);

        if (is_bool($valueOrLevel)) {
            return $valueOrLevel ? 'Active' : 'Not active';
        }

        return 'Level: '.$valueOrLevel;
    }

    /**
     * Returns the printable prepared set property (state).
     */
    private function getImportNameState(string $key): string
    {
        return $this->parameters->getImportName($key) ? 'Active' : 'Not active';
    }
}
