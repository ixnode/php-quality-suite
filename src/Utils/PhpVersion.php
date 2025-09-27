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

namespace Ixnode\PhpQualitySuite\Utils;

/**
 * Class PhpVersion
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-27)
 * @since 1.0.0 (2025-09-27) First version
 */
final class PhpVersion
{
    /**
     */
    public function __construct(private int $phpVersion)
    {
    }

    /**
     * Returns the int representation of the PHP version.
     */
    public function getInt(): int
    {
        return $this->phpVersion;
    }

    /**
     * Formats the rector php version into a readable string format.
     */
    public function getString(string $template = '%d.%d.%s'): string
    {
        $major = intdiv($this->phpVersion, 10000);
        $minor = intdiv($this->phpVersion % 10000, 100);
        $patch = $this->phpVersion % 100;

        return sprintf($template, $major, $minor, $patch);
    }

    /**
     * Formats the rector php version into number format.
     */
    public function getNumber(): float
    {
        $major = intdiv($this->phpVersion, 10000);
        $minor = intdiv($this->phpVersion % 10000, 100);

        return $major + $minor * 0.1;
    }
}
