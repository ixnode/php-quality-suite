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

/**
 * Class Paths
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 */
class Paths
{
    public function __construct(private Parameters $parameters)
    {
    }

    /**
     * Returns all available paths.
     *
     * @return string[]
     */
    public function getAll(): array
    {
        return array_values($this->parameters->getPathsIncluded());
    }

    /**
     * Selects and returns only certain paths.
     *
     * @return string[]
     */
    public function getOnly(string ...$keys): array
    {
        return array_values(array_intersect_key($this->parameters->getPathsIncluded(), array_flip($keys)));
    }

    /**
     * Excludes and returns certain paths.
     *
     * @return string[]
     */
    public function getWithout(string ...$keys): array
    {
        return array_values(array_diff_key($this->parameters->getPathsIncluded(), array_flip($keys)));
    }
}
