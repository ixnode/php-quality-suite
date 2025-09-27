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

use Ixnode\PhpQualitySuite\Configuration\Rules\RulesExcluded;
use Ixnode\PhpQualitySuite\Configuration\Rules\RulesIncluded;
use Ixnode\PhpQualitySuite\Paths;
use RuntimeException;

/**
 * Class PqsConfiguration
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-25)
 * @since 1.0.0 (2025-09-25) First version
 */
final class PqsConfiguration
{
    private const PATH_CONFIG_1 = 'config/pqs.yml';

    private const PATH_CONFIG_2 = 'pqs.yml';

    private const PATH_CONFIG_DIST = __DIR__.'/../../config/pqs.yml.dist';

    private array $config;

    /**
     */
    public function __construct(private Paths $paths)
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
     * @param string[] $include
     * @return string[]
     */
    public function getPathsIncludedFiltered(array $include): array
    {
        return array_map(
            static fn(string $path): string => $path,
            $include === [] ? $this->paths->getAll() : $this->paths->getOnly(...$include)
        );
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
    public function getPathsExcludedFiltered(): array
    {
        return array_map(
            static fn(string $path): string => $path,
            $this->getPathsExcluded()
        );
    }

    /**
     * @return array<string, string>
     */
    public function getRulesIncluded(): array
    {
        return $this->config['rules-included'] ?? [];
    }

    /**
     * @param string[] $rules
     * @return string[]|null
     */
    public function getRulesIncludedFiltered(array $rules): array|null
    {
        if ($rules === []) {
            return null;
        }

        return (new RulesIncluded($this))->getOnly(...$rules);
    }

    /**
     * @return string[]
     */
    public function getRulesExcluded(): array
    {
        return $this->config['rules-excluded'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getRulesExcludedFiltered(float|null $phpVersion = null, float|null $symfonyVersion = null): array
    {
        return (new RulesExcluded($this))->get(
            phpVersion: $phpVersion,
            symfonyVersion: $symfonyVersion
        );
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
