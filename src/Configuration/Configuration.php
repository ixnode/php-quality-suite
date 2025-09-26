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

namespace Ixnode\PhpQualitySuite\Configuration;

use RuntimeException;

/**
 * Class Configuration
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-25)
 * @since 1.0.0 (2025-09-25) First version
 */
final class Configuration
{
    private const PATH_CONFIG_1 = 'config/pqs.yml';

    private const PATH_CONFIG_2 = 'pqs.yml';

    private const PATH_CONFIG_DIST = __DIR__.'/../../config/pqs.yml.dist';

    private array $config;

    /**
     */
    public function __construct()
    {
        $this->parseYamlFile();
    }

    /**
     * @return array<string, string>
     */
    public function getPathsIncluded(): array
    {
        return $this->config['paths-included'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getPathsExcluded(): array
    {
        return $this->config['paths-excluded'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getRulesExcluded(): array
    {
        return $this->config['rules-excluded'] ?? [];
    }

    /**
     * Parses the pqs.yml file.
     */
    private function parseYamlFile(): void
    {
        $pathConfig = match (true) {
            file_exists(self::PATH_CONFIG_1) => self::PATH_CONFIG_1,
            file_exists(self::PATH_CONFIG_2) => self::PATH_CONFIG_2,
            file_exists(self::PATH_CONFIG_DIST) => self::PATH_CONFIG_DIST,
            default => null,
        };

        if (is_null($pathConfig)) {
            throw new RuntimeException(sprintf('Config file not found: %s', self::PATH_CONFIG_1));
        }

        if (!function_exists('yaml_parse_file')) {
            throw new RuntimeException(
                'The YAML extension (ext-yaml) is not installed. ' .
                'Please install it (e.g. "sudo apt install php-yaml" or "pecl install yaml").'
            );
        }

        $parsed = yaml_parse_file($pathConfig);

        if ($parsed === false || $parsed === null) {
            throw new RuntimeException(sprintf('Failed to parse YAML file: %s', $pathConfig));
        }

        if (!is_array($parsed)) {
            throw new RuntimeException(sprintf(
                'Unexpected YAML structure in %s: expected array, got %s',
                $pathConfig,
                gettype($parsed)
            ));
        }

        $this->config = $parsed;
    }
}
