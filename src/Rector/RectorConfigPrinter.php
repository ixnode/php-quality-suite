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

    private const LENGTH_SEPARATOR = 50;

    /**
     */
    public function __construct(RectorConfigBuilder $rectorConfigBuilder)
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
        $printed = (int)getenv('RECTOR_OVERVIEW_PRINTED');

        if ($printed >= ($this->debug ? 2 : 1)) {

            if ($this->debug) {
                exit();
            }

            return;
        }

        $this->printMain();
        $this->printDetail();

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
        $phpVersion = new PhpVersion(ComposerJsonPhpVersionResolver::resolveFromCwdOrFail());

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
        echo "Rector Overview".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;

        /* A */
        echo sprintf("With php version:                   %s (%.1f)", $phpVersion->getString('%d.%d.x'), $phpVersion->getNumber()).PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* B */
        echo sprintf("Included paths:                     %s", $includedPaths === [] ? 'all' : implode(', ', $includedPaths)).PHP_EOL;
        echo sprintf("Level:                              %s", $level ?? 'N/A').PHP_EOL;
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
     * Print detailed information.
     */
    private function printDetail(): void
    {
        if (!$this->parameters->isDetails()) {
            return;
        }

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "Prepared Set Details".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo sprintf("deadCode:              %s", $this->getRuleState('deadCode')).PHP_EOL;
        echo sprintf("codeQuality:           %s", $this->getRuleState('codeQuality')).PHP_EOL;
        echo sprintf("codingStyle:           %s", $this->getRuleState('codingStyle')).PHP_EOL;
        echo sprintf("typeDeclarations:      %s", $this->getRuleState('typeDeclarations')).PHP_EOL;
        echo sprintf("privatization:         %s", $this->getRuleState('privatization')).PHP_EOL;
        echo sprintf("naming:                %s", $this->getRuleState('naming')).PHP_EOL;
        echo sprintf("instanceOf:            %s", $this->getRuleState('instanceOf')).PHP_EOL;
        echo sprintf("earlyReturn:           %s", $this->getRuleState('earlyReturn')).PHP_EOL;
        echo sprintf("strictBooleans:        %s", $this->getRuleState('strictBooleans')).PHP_EOL;
        echo sprintf("carbon:                %s", $this->getRuleState('carbon')).PHP_EOL;
        echo sprintf("rectorPreset:          %s", $this->getRuleState('rectorPreset')).PHP_EOL;
        echo sprintf("phpunitCodeQuality:    %s", $this->getRuleState('phpunitCodeQuality')).PHP_EOL;
        echo sprintf("doctrineCodeQuality:   %s", $this->getRuleState('doctrineCodeQuality')).PHP_EOL;
        echo sprintf("symfonyCodeQuality:    %s", $this->getRuleState('symfonyCodeQuality')).PHP_EOL;
        echo sprintf("symfonyConfigs:        %s", $this->getRuleState('symfonyConfigs')).PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }

    /**
     * Returns the printable rule property (state).
     */
    private function getRuleState(string $key): string
    {
        $valueOrLevel = $this->parameters->getPreparedSetOrPreparedSetLevel($key);

        if (is_bool($valueOrLevel)) {
            return $valueOrLevel ? 'Active' : 'Not active';
        }

        return 'Level: '.$valueOrLevel;
    }
}
