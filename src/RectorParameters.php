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

use InvalidArgumentException;
use Rector\Symfony\Set\SymfonySetList;
use RuntimeException;

/**
 * Class RectorParameters
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 */
final class RectorParameters
{
    private const PATH_CONFIG = 'paths.yaml';

    private const PATH_CONFIG_DIST = __DIR__.'/../paths.yaml.dist';

    /** @var array<string, bool>
     */
    public const DEFAULT_RULES = [
        'all' => false,
        'deadCode' => false,
        'codeQuality' => false,
        'codingStyle' => false,
        'typeDeclarations' => false,
        'privatization' => false,
        'naming' => false,
        'instanceOf' => false,
        'earlyReturn' => false,
        'strictBooleans' => false,
        'carbon' => false,
        'rectorPreset' => false,
        'phpunitCodeQuality' => false,
        'doctrineCodeQuality' => false,
        'symfonyCodeQuality' => false,
        'symfonyConfigs' => false,
    ];

    /** @var array<string, int|null> */
    public const DEFAULT_RULE_LEVELS = [
        'deadCode' => null,
        'codeQuality' => null,
        'codingStyle' => null,
        'typeDeclarations' => null,
        'privatization' => null,
        'naming' => null,
        'instanceOf' => null,
        'earlyReturn' => null,
        'strictBooleans' => null,
        'carbon' => null,
        'rectorPreset' => null,
        'phpunitCodeQuality' => null,
        'doctrineCodeQuality' => null,
        'symfonyCodeQuality' => null,
        'symfonyConfigs' => null,
    ];

    private const ALLOWED_KEYS_WITH_LEVELS = ['deadCode', 'codeQuality', 'codingStyle', 'typeDeclarations'];

    public const ALLOWED_SYMFONY_VERSIONS = [
        '2.5' => SymfonySetList::SYMFONY_25,
        '2.6' => SymfonySetList::SYMFONY_26,
        '2.7' => SymfonySetList::SYMFONY_27,
        '2.8' => SymfonySetList::SYMFONY_28,
        '3.0' => SymfonySetList::SYMFONY_30,
        '3.1' => SymfonySetList::SYMFONY_31,
        '3.2' => SymfonySetList::SYMFONY_32,
        '3.3' => SymfonySetList::SYMFONY_33,
        '3.4' => SymfonySetList::SYMFONY_34,
        '4.0' => SymfonySetList::SYMFONY_40,
        '4.1' => SymfonySetList::SYMFONY_41,
        '4.2' => SymfonySetList::SYMFONY_42,
        '4.3' => SymfonySetList::SYMFONY_43,
        '4.4' => SymfonySetList::SYMFONY_44,
        '5.0' => SymfonySetList::SYMFONY_50,
        '5.1' => SymfonySetList::SYMFONY_51,
        '5.2' => SymfonySetList::SYMFONY_52,
        '5.3' => SymfonySetList::SYMFONY_53,
        '5.4' => SymfonySetList::SYMFONY_54,
        '6.0' => SymfonySetList::SYMFONY_60,
        '6.1' => SymfonySetList::SYMFONY_61,
        '6.2' => SymfonySetList::SYMFONY_62,
        '6.3' => SymfonySetList::SYMFONY_63,
        '6.4' => SymfonySetList::SYMFONY_64,
        '7.0' => SymfonySetList::SYMFONY_70,
        '7.1' => SymfonySetList::SYMFONY_71,
        '7.2' => SymfonySetList::SYMFONY_72,
        '7.3' => SymfonySetList::SYMFONY_73,
        '7.4' => SymfonySetList::SYMFONY_74,
    ];

    private array $config;

    private bool $details;

    private int|null $level = null;

    /** @var string[] */
    private array $includedPaths;

    /** @var array<string, bool> */
    private array $rules;

    /** @var array<string, null|int> */
    private array $ruleLevels;

    private string|null $withSymfony = null;

    private bool $withSymfonyCodeQuality;

    private bool $withSymfonyConstructorInjection;

    /**
     */
    public function __construct()
    {
        $this->parseYamlFile();
        $this->parseArgs();
        $this->hydrateFromEnv();
    }

    /**
     * @return array<string, string>
     */
    public function getPaths(): array
    {
        return $this->config['paths'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getPathsExcluded(): array
    {
        return $this->config['excluded'] ?? [];
    }

    /**
     * Returns whether to show detailed information.
     */
    public function getDetails(bool $default = false): bool
    {
        return $this->details ?? $default;
    }

    /**
     * Returns the used PHP level analyzed by rector.
     */
    public function getLevel(int|null $default = null): int|null
    {
        return $this->level ?? $default;
    }

    /**
     * Returns all included paths analyzed by rector.
     *
     * @return string[]
     */
    public function getIncludedPaths(array $default = []): array
    {
        return $this->includedPaths ?? $default;
    }

    /**
     * Returns the rule list analyzed by rector.
     *
     * @return array<string, bool>
     */
    public function getRules(array $default = self::DEFAULT_RULES): array
    {
        return $this->rules ?? $default;
    }

    /**
     * Returns the rule level list analyzed by rector.
     *
     * @return array<string, int|null>
     */
    public function getRuleLevels(array $default = self::DEFAULT_RULE_LEVELS): array
    {
        return $this->ruleLevels ?? $default;
    }

    /**
     * Returns the rule (active or not).
     *
     * @param array<string, bool> $defaultRules
     */
    public function getRule(string $key, array $defaultRules = self::DEFAULT_RULES): bool
    {
        $ruleLevel = $this->getRuleLevel($key);

        if (!is_null($ruleLevel)) {
            return false;
        }

        $rules = $this->getRules($defaultRules);

        if ($rules['all'] === true) {
            return true;
        }

        if (!array_key_exists($key, $rules)) {
            throw new InvalidArgumentException('Invalid rule key: '.$key);
        }

        return $rules[$key];
    }

    /**
     * Returns the rule level.
     *
     * @param array<string, int|null> $defaultRuleLevels
     */
    public function getRuleLevel(string $key, array $defaultRuleLevels = self::DEFAULT_RULE_LEVELS): int|null
    {
        $ruleLevels = $this->getRuleLevels($defaultRuleLevels);

        if (!array_key_exists($key, $ruleLevels)) {
            throw new InvalidArgumentException('Invalid rule level key: '.$key);
        }

        return $ruleLevels[$key];
    }

    /**
     * Returns the rule (active or not) or rule level.
     *
     * @param array<string, bool> $defaultRules
     * @param array<string, int|null> $defaultRuleLevels
     */
    public function getRuleOrRuleLevel(
        string $key,
        array $defaultRules = self::DEFAULT_RULES,
        array $defaultRuleLevels = self::DEFAULT_RULE_LEVELS
    ): int|bool
    {
        $ruleLevel = $this->getRuleLevel($key, $defaultRuleLevels);

        if (!is_null($ruleLevel)) {
            return $ruleLevel;
        }

        return $this->getRule($key, $defaultRules);
    }

    /**
     * Returns the wanted symfony version analyzed by rector.
     */
    public function getWithSymfony(string|null $default = null): string|null
    {
        return $this->withSymfony ?? $default;
    }

    /**
     * Returns whether to analyze symfony code quality.
     */
    public function getWithSymfonyCodeQuality(bool $default = false): bool
    {
        return $this->withSymfonyCodeQuality ?? $default;
    }

    /**
     * Returns whether to show detailed information.
     */
    public function getWithSymfonyConstructorInjection(bool $default = false): bool
    {
        return $this->withSymfonyConstructorInjection ?? $default;
    }

    /**
     * Parses the custom rector arguments.
     */
    private function parseArgs(): void
    {
        if (!isset($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
            return;
        }

        foreach ($_SERVER['argv'] as $i => $arg) {
            if (!is_string($arg)) {
                continue;
            }

            if (!str_starts_with($arg, '--')) {
                continue;
            }

            if (str_starts_with($arg, '--details')) {
                $this->details = true;
                putenv("RECTOR_DETAILS=1");
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--level=')) {
                $value = (int)substr($arg, strlen('--level='));
                $this->level = $value;
                putenv('RECTOR_LEVEL='.$value);
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--include=')) {
                $list = $this->splitList(substr($arg, strlen('--include=')));

                $allowed = array_keys($this->getPaths());
                $invalid = array_diff($list, $allowed);

                if ($invalid !== []) {
                    throw new InvalidArgumentException(
                        'Invalid include keys: ' . implode(', ', $invalid) .
                        '. Allowed keys: ' . implode(', ', $allowed)
                    );
                }

                $this->includedPaths = $list;
                putenv('RECTOR_INCLUDE=' . implode(',', $list));
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--rules=')) {
                $list = $this->splitList(substr($arg, strlen('--rules=')));

                $allowed = array_keys(self::DEFAULT_RULES);

                $this->rules = self::DEFAULT_RULES;
                $this->ruleLevels = self::DEFAULT_RULE_LEVELS;

                foreach ($list as $entry) {
                    [$key, $level] = array_pad(explode(':', $entry, 2), 2, null);

                    if (!in_array($key, $allowed, true)) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid rule key "%s". Allowed keys: %s',
                                $key,
                                implode(', ', $allowed)
                            )
                        );
                    }

                    if ($level !== null) {
                        $keysWithLevelsAllowed = ['deadCode', 'codeQuality', 'codingStyle', 'typeDeclarations'];
                        if (!in_array($key, $keysWithLevelsAllowed, true)) {
                            throw new InvalidArgumentException(
                                sprintf('Rule key "%s" does not accept levels (got "%s")', $key, $level)
                            );
                        }

                        $this->ruleLevels[$key] = (int)$level;
                    } else {
                        $this->rules[$key] = true;
                    }
                }

                putenv('RECTOR_RULES=' . implode(',', $list));
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--with-symfony=')) {
                $value = substr($arg, strlen('--with-symfony='));

                if (!array_key_exists($value, self::ALLOWED_SYMFONY_VERSIONS)) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid Symfony version "%s". Allowed versions are: %s',
                        $value,
                        implode(', ', array_keys(self::ALLOWED_SYMFONY_VERSIONS))
                    ));
                }

                $this->withSymfony = $value;
                putenv('RECTOR_WITH_SYMFONY='.$value);
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--with-symfony-code-quality')) {
                $this->withSymfonyCodeQuality = true;
                putenv("RECTOR_WITH_SYMFONY_CODE_QUALITY=1");
                unset($_SERVER['argv'][$i]);
                continue;
            }

            if (str_starts_with($arg, '--with-symfony-constructor-injection')) {
                $this->withSymfonyConstructorInjection = true;
                putenv("RECTOR_WITH_SYMFONY_CONSTRUCTOR_INJECTION=1");
                unset($_SERVER['argv'][$i]);
                continue;
            }
        }

        $_SERVER['argv'] = array_values($_SERVER['argv']);
    }

    /**
     * Parses the custom rector env variables.
     */
    private function hydrateFromEnv(): void
    {
        if (!isset($this->details)) {
            $env = getenv('RECTOR_DETAILS');
            $this->details = $env !== false && $env !== '';
        }

        if (is_null($this->level)) {
            $env = getenv('RECTOR_LEVEL');
            if ($env !== false && $env !== '') {
                $this->level = (int) $env;
            }
        }

        if (!isset($this->includedPaths)) {
            $env = getenv('RECTOR_INCLUDE');
            if ($env !== false && $env !== '') {
                $this->includedPaths = $this->splitList($env);
            }
        }

        if (!isset($this->rules) || !isset($this->ruleLevels)) {
            $env = getenv('RECTOR_RULES');

            if ($env !== false && $env !== '') {
                $list = $this->splitList($env);

                $this->rules = self::DEFAULT_RULES;
                $this->ruleLevels = self::DEFAULT_RULE_LEVELS;

                foreach ($list as $entry) {
                    [$key, $level] = array_pad(explode(':', $entry, 2), 2, null);

                    if ($level !== null) {
                        if (!in_array($key, self::ALLOWED_KEYS_WITH_LEVELS, true)) {
                            throw new InvalidArgumentException(
                                sprintf('Rule key "%s" does not accept levels (got "%s")', $key, $level)
                            );
                        }

                        $this->ruleLevels[$key] = (int)$level;
                    } else {
                        $this->rules[$key] = true;
                    }
                }
            }
        }

        if (is_null($this->withSymfony)) {
            $env = getenv('RECTOR_WITH_SYMFONY');
            if ($env !== false && $env !== '') {
                $this->withSymfony = $env;
            }
        }

        if (!isset($this->withSymfonyCodeQuality)) {
            $env = getenv('RECTOR_WITH_SYMFONY_CODE_QUALITY');
            $this->withSymfonyCodeQuality = $env !== false && $env !== '';
        }

        if (!isset($this->withSymfonyConstructorInjection)) {
            $env = getenv('RECTOR_WITH_SYMFONY_CONSTRUCTOR_INJECTION');
            $this->withSymfonyConstructorInjection = $env !== false && $env !== '';
        }
    }

    /**
     * Splits a given string list by comma.
     *
     * @return string[]
     */
    private function splitList(string $value): array
    {
        $items = array_map('trim', explode(',', $value));
        $items = array_filter($items, static fn($v): bool => $v !== '');
        return array_values(array_unique($items));
    }

    /**
     * Parses the paths.yaml file.
     */
    private function parseYamlFile(): void
    {
        $pathConfig = match (true) {
            file_exists(self::PATH_CONFIG) => self::PATH_CONFIG,
            file_exists(self::PATH_CONFIG_DIST) => self::PATH_CONFIG_DIST,
            default => null,
        };

        if (is_null($pathConfig)) {
            throw new RuntimeException(sprintf('Config file not found: %s', self::PATH_CONFIG));
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
