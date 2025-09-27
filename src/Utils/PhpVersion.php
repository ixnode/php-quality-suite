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

use InvalidArgumentException;

/**
 * Class PhpVersion
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-27)
 * @since 1.0.0 (2025-09-27) First version
 */
final class PhpVersion
{
    private int $phpVersion;

    /**
     */
    public function __construct(int|string $phpVersion)
    {
        $this->phpVersion = match (true) {
            is_string($phpVersion) => $this->parsePhpVersion($phpVersion),
            default => $phpVersion,
        };
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

    /**
     * Parses a given string PHP version.
     */
    private function parsePhpVersion(string $phpVersion): int
    {
        $parts = explode('.', $phpVersion);

        if (count($parts) < 3) {
            throw new InvalidArgumentException(
                sprintf('Invalid PHP version format: "%s". Expected format "X.Y.Z".', $phpVersion)
            );
        }

        [$major, $minor, $release] = array_map('intval', array_slice($parts, 0, 3));

        return $major * 10000 + $minor * 100 + $release;
    }
}
