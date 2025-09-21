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
 * Class Parameters
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 */
final class Parameters
{
    private const PATH_CONFIG_1 = 'config/pqs.yml';

    private const PATH_CONFIG_2 = 'pqs.yml';

    private const PATH_CONFIG_DIST = __DIR__.'/../config/pqs.yml.dist';

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

    private const ARGUMENT_MAPPING = [
        'type'                               => ['RECTOR_TYPE',                               true,  ['rector']],
        'details'                            => ['RECTOR_DETAILS',                            false, ['rector']],
        'level'                              => ['RECTOR_LEVEL',                              true,  ['rector']],
        'include'                            => ['RECTOR_INCLUDES',                           true,  ['rector']],
        'rules'                              => ['RECTOR_RULES',                              true,  ['rector']],
        'with-symfony'                       => ['RECTOR_WITH_SYMFONY',                       true,  ['rector']],
        'with-symfony-code-quality'          => ['RECTOR_WITH_SYMFONY_CODE_QUALITY',          false, ['rector']],
        'with-symfony-constructor-injection' => ['RECTOR_WITH_SYMFONY_CONSTRUCTOR_INJECTION', false, ['rector']],
    ];

    private string $type;

    /** @var array<string, string> $arguments */
    private array $arguments = [];

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
        $this->parseEnv();
        $this->adoptArguments();
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

            foreach (self::ARGUMENT_MAPPING as $prefix => [$envKey, $hasValue]) {
                $argumentName = '--'.$prefix.($hasValue ? '=' : '');
                if (str_starts_with($arg, $argumentName)) {
                    $value = $hasValue ? substr($arg, strlen($argumentName)) : '1';
                    $argumentKey = $prefix;

                    $this->arguments[$argumentKey] = $value;
                    putenv($envKey.'='.$value);

                    unset($_SERVER['argv'][$i]);
                    break;
                }
            }
        }

        $_SERVER['argv'] = array_values($_SERVER['argv']);
    }

    /**
     * Parses the custom rector env variables.
     */
    private function parseEnv(): void
    {
        foreach (self::ARGUMENT_MAPPING as $argKey => [$envKey]) {
            if (!array_key_exists($argKey, $this->arguments)) {
                $env = getenv($envKey);
                if ($env !== false && $env !== '') {
                    $this->arguments[$argKey] = $env;
                }
            }
        }
    }

    /**
     * Adopt all collected arguments.
     */
    private function adoptArguments(): void
    {
        $this->type = $this->arguments['type'] ?? 'keras';

        foreach ($this->arguments as $key => $value) {
            $allowedTypes = self::ARGUMENT_MAPPING[$key][2] ?? [];
            if ($allowedTypes && !in_array($this->type, $allowedTypes, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument "%s" is not allowed in "%s" context.',
                    $key,
                    $this->type
                ));
            }

            switch ($key) {
                case 'details':
                    $this->details = true;
                    break;

                case 'level':
                    $this->level = (int) $value;
                    break;

                case 'includes':
                    $this->adoptIncludes($value);
                    break;

                case 'rules':
                    $this->adoptRules($value);
                    break;

                case 'with-symfony':
                    $this->adoptSymfonyVersion($value);
                    break;

                case 'with-symfony-code-quality':
                    $this->withSymfonyCodeQuality = true;
                    break;

                case 'with-symfony-constructor-injection':
                    $this->withSymfonyConstructorInjection = true;
                    break;
            }
        }
    }

    /**
     * Adopt argument "includes".
     */
    private function adoptIncludes(string $value): void
    {
        $list = $this->splitList($value);

        $allowed = array_keys($this->getPathsIncluded());
        $invalid = array_diff($list, $allowed);

        if ($invalid !== []) {
            throw new InvalidArgumentException(
                'Invalid include keys: ' . implode(', ', $invalid) . '. ' .
                'Allowed keys: ' . implode(', ', $allowed)
            );
        }

        $this->includedPaths = $list;
    }

    /**
     * Adopt argument "rules".
     */
    private function adoptRules(string $value): void
    {
        $list = $this->splitList($value);

        $allowed = array_keys(self::DEFAULT_RULES);

        $this->rules = self::DEFAULT_RULES;
        $this->ruleLevels = self::DEFAULT_RULE_LEVELS;

        foreach ($list as $entry) {
            [$key, $level] = array_pad(explode(':', $entry, 2), 2, null);

            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid rule key "%s". Allowed keys: %s',
                    $key,
                    implode(', ', $allowed)
                ));
            }

            if ($level !== null) {
                if (!in_array($key, self::ALLOWED_KEYS_WITH_LEVELS, true)) {
                    throw new InvalidArgumentException(
                        sprintf('Rule key "%s" does not accept levels (got "%s")', $key, $level)
                    );
                }

                $this->ruleLevels[$key] = (int) $level;
            } else {
                $this->rules[$key] = true;
            }
        }
    }

    /**
     * Adopt argument "with-symfony".
     */
    private function adoptSymfonyVersion(string $value): void
    {
        if (!array_key_exists($value, self::ALLOWED_SYMFONY_VERSIONS)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid Symfony version "%s". Allowed versions are: %s',
                $value,
                implode(', ', array_keys(self::ALLOWED_SYMFONY_VERSIONS))
            ));
        }

        $this->withSymfony = $value;
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
