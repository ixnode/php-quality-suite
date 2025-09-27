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

namespace Ixnode\PhpQualitySuite\Configuration\Rules;

use Ixnode\PhpQualitySuite\Configuration\PqsConfiguration;

/**
 * Class RulesIncluded
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-27)
 * @since 1.0.0 (2025-09-27) First version
 */
final class RulesIncluded
{
    /** @var string[] */
    private array $rulesIncluded;

    /**
     * @param PqsConfiguration|string[] $configuration
     */
    public function __construct(PqsConfiguration|array $configuration)
    {
        $this->rulesIncluded = match (true) {
            $configuration instanceof PqsConfiguration => $configuration->getRulesIncluded(),
            default => $configuration,
        };
    }

    /**
     * Returns all available included rules.
     *
     * @return string[]
     */
    public function getAll(): array
    {
        return array_values($this->rulesIncluded);
    }

    /**
     * Selects and returns only certain included rules.
     *
     * @return string[]
     */
    public function getOnly(string ...$keys): array
    {
        return array_values(array_intersect_key($this->rulesIncluded, array_flip($keys)));
    }

    /**
     * Excludes and returns certain included rules.
     *
     * @return string[]
     */
    public function getWithout(string ...$keys): array
    {
        return array_values(array_diff_key($this->rulesIncluded, array_flip($keys)));
    }
}
