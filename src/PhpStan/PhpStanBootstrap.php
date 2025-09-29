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

/**
 * Class PhpStanBootstrap
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-28)
 * @since 1.0.0 (2025-09-28) First version
 */
final class PhpStanBootstrap
{
    private PhpStanConfigBuilder $phpStanConfigBuilder;

    /**
     */
    public function __construct()
    {
        $this->phpStanConfigBuilder = new PhpStanConfigBuilder();
    }

    /**
     * Returns all PHPStan parameters.
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        $parameters = $this->phpStanConfigBuilder->getCliParameters();

        (new PhpStanConfigPrinter($this->phpStanConfigBuilder))->print();

        return $parameters;
    }
}
