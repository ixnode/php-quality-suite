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

namespace Ixnode\PhpQualitySuite\PhpStan;

use Ixnode\PhpQualitySuite\Parameters;
use Ixnode\PhpQualitySuite\Utils\PhpVersion;
use Rector\Exception\Configuration\InvalidConfigurationException;

/**
 * Class PhpStanConfigPrinter
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-27)
 * @since 1.0.0 (2025-09-27) First version
 */
final class PhpStanConfigPrinter
{
    private Parameters $parameters;

    private bool $debug = false;

    public const LENGTH_SEPARATOR = 50;

    /**
     */
    public function __construct(private PhpStanConfigBuilder $phpStanConfigBuilder)
    {
        $this->parameters = $phpStanConfigBuilder->getParameters();
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Prints the current PHPStan setup.
     */
    public function print(): void
    {
        $printed = (int) getenv('PHPSTAN_OVERVIEW_PRINTED');

        if ($printed >= ($this->debug ? 2 : 1)) {

            if ($this->debug) {
                exit();
            }

            return;
        }

        $this->printMain();

        echo PHP_EOL;
        echo PHP_EOL;

        ++$printed;

        putenv('PHPSTAN_OVERVIEW_PRINTED='.$printed);
    }

    /**
     * Print the main information.
     */
    private function printMain(): void
    {
        /* A */
        $phpVersionRunning = new PhpVersion(phpversion());

        /* B */
        $includedPaths = $this->parameters->getInclude();
        $level = $this->parameters->getLevel();

        /* D */
        $isDetails = $this->parameters->isDetails();
        $isDryRun = $this->parameters->isDryRun();

        echo PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
        echo "I) PHPStan Overview".PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;

        /* A */
        echo sprintf('Running PHP version:                %s (%.1f)', $phpVersionRunning->getString(), $phpVersionRunning->getNumber()).PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* B */
        echo sprintf("Included paths:                     %s", $includedPaths === [] ? 'all' : implode(', ', $includedPaths)).PHP_EOL;
        echo sprintf("Level:                              %s", $level ?? 'max').PHP_EOL;
        echo str_repeat('-', self::LENGTH_SEPARATOR).PHP_EOL;

        /* D */
        echo sprintf("Show details:                       %s", $isDetails ? 'Yes' : 'No').PHP_EOL;
        echo sprintf("Dry run mode:                       %s", $isDryRun ? 'Yes' : 'No').PHP_EOL;
        echo str_repeat('=', self::LENGTH_SEPARATOR).PHP_EOL;
    }
}
