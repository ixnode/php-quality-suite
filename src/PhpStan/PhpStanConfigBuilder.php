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

/**
 * Class PhpStanConfigBuilder
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-28)
 * @since 1.0.0 (2025-09-28) First version
 */
class PhpStanConfigBuilder
{
    private Parameters $parameters;

    /**
     */
    public function __construct()
    {
        $this->parameters = new Parameters();
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * Returns all PHPStan parameters.
     *
     * @return string[]
     */
    public function getCliParameters(): array
    {
        $level = $this->parameters->getLevel();

        $parameters = [];

        $parameters = array_merge($parameters, array_values($this->parameters->getPathsIncludedFiltered()));

        if (!is_null($level)) {
            $parameters = array_merge($parameters, ['--level', $level]);
        }

        return $parameters;
    }
}
