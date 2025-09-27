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
use Ixnode\PhpQualitySuite\Configuration\PqsConfiguration;
use Ixnode\PhpQualitySuite\Configuration\Rules\RulesExcluded;
use Rector\Symfony\Set\SymfonySetList;

/**
 * Class Parameters
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 */
final class Parameters
{
    /** @var array<string, bool>
     */
    public const DEFAULT_PREPARED_SETS = [
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
    public const DEFAULT_PREPARED_SET_LEVELS = [
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

    private const ALLOWED_PREPARED_SET_KEYS_WITH_LEVELS = ['deadCode', 'codeQuality', 'codingStyle', 'typeDeclarations'];

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
        /* [env-key, has-value, remove-argument, scope-of-validity] */

        'include'                            => ['PQS_INCLUDE',                            true,  true,  ['rector']],
        'level'                              => ['PQS_LEVEL',                              true,  true,  ['rector']],
        'sets'                               => ['PQS_SETS',                               true,  true,  ['rector']],
        'rules'                              => ['PQS_RULES',                              true,  true,  ['rector']],

        'with-symfony'                       => ['PQS_WITH_SYMFONY',                       true,  true,  ['rector']],
        'with-symfony-code-quality'          => ['PQS_WITH_SYMFONY_CODE_QUALITY',          false, true,  ['rector']],
        'with-symfony-constructor-injection' => ['PQS_WITH_SYMFONY_CONSTRUCTOR_INJECTION', false, true,  ['rector']],

        'details'                            => ['PQS_DETAILS',                            false, true,  ['rector']],
        'dry-run'                            => ['PQS_DRY_RUN',                            false, false, ['rector']],

        'type'                               => ['PQS_TYPE',                               true,  true,  ['rector']],
    ];

    private PqsConfiguration $configuration;

    private Paths $paths;

    /** @var array<string, string> $arguments */
    private array $arguments = [];

    /** @var string[] */
    private array $include;

    private int|null $level = null;

    /** @var array<string, bool> */
    private array $preparedSets;

    /** @var array<string, null|int> */
    private array $preparedSetLevels;

    /** @var string[] */
    private array $rules;

    private string|null $withSymfony = null;

    private bool $withSymfonyCodeQuality;

    private bool $withSymfonyConstructorInjection;

    private bool $details;

    private bool $dryRun;

    /**
     */
    public function __construct()
    {
        $this->paths = new Paths($this);
        $this->configuration = new PqsConfiguration($this->paths);

        $this->parseArgs();
        $this->parseEnv();
        $this->adoptArguments();
    }

    /**
     * Configuration alias: getPathsIncluded
     *
     * @return array<string, string>
     */
    public function getPathsIncluded(): array
    {
        return $this->configuration->getPathsIncluded();
    }

    /**
     * Configuration alias: getPathsIncludedFiltered
     *
     * @return array<string, string>
     */
    public function getPathsIncludedFiltered(): array
    {
        return $this->configuration->getPathsIncludedFiltered(include: $this->getInclude());
    }

    /**
     * Configuration alias: getPathsExcluded
     *
     * @return string[]
     */
    public function getPathsExcluded(): array
    {
        return $this->configuration->getPathsExcluded();
    }

    /**
     * Configuration alias: getPathsExcluded
     *
     * @return string[]
     */
    public function getPathsExcludedFiltered(): array
    {
        return $this->configuration->getPathsExcluded();
    }

    /**
     * Configuration alias: getRulesIncluded
     *
     * @return array<string, string>
     */
    public function getRulesIncluded(): array
    {
        return $this->configuration->getRulesIncluded();
    }

    /**
     * Configuration alias: getRulesIncluded
     *
     * @return string[]|null
     */
    public function getRulesIncludedFiltered(): array|null
    {
        return $this->configuration->getRulesIncludedFiltered(rules: $this->getRules());
    }

    /**
     * Configuration alias: hasRulesIncludedFiltered
     */
    public function hasRulesIncludedFiltered(): bool
    {
        return !is_null($this->getRulesIncludedFiltered());
    }

    /**
     * Configuration alias: getRulesExcluded
     *
     * @return string[]
     */
    public function getRulesExcluded(): array
    {
        return $this->configuration->getRulesExcluded();
    }

    /**
     * Configuration alias: getRulesExcludedFiltered
     *
     * @return string[]
     */
    public function getRulesExcludedFiltered(float|null $phpVersion = null, float|null $symfonyVersion = null): array
    {
        return $this->configuration->getRulesExcludedFiltered(
            phpVersion: $phpVersion,
            symfonyVersion: $symfonyVersion,
        );
    }

    /**
     * Returns all included paths analyzed by rector.
     *
     * Parameter: -i,--include
     *
     * @return string[]
     */
    public function getInclude(array $default = []): array
    {
        return $this->include ?? $default;
    }

    /**
     * Returns the used PHP level analyzed by rector.
     *
     * Parameter: -l,--level
     */
    public function getLevel(int|null $default = null): int|null
    {
        return $this->level ?? $default;
    }

    /**
     * Returns the prepared set list analyzed by rector.
     *
     * Parameter: -s,--sets
     *
     * @return array<string, bool>
     */
    public function getPreparedSets(array $default = self::DEFAULT_PREPARED_SETS): array
    {
        return $this->preparedSets ?? $default;
    }

    /**
     * Returns the prepared set level list analyzed by rector.
     *
     * Parameter: -s,--sets
     *
     * @return array<string, int|null>
     */
    public function getPreparedSetLevels(array $default = self::DEFAULT_PREPARED_SET_LEVELS): array
    {
        return $this->preparedSetLevels ?? $default;
    }

    /**
     * Returns the prepared set (active or not).
     *
     * Parameter: -s,--sets
     *
     * @param array<string, bool> $defaultRules
     */
    public function getPreparedSet(string $key, array $defaultRules = self::DEFAULT_PREPARED_SETS): bool
    {
        $ruleLevel = $this->getPreparedSetLevel($key);

        if (!is_null($ruleLevel)) {
            return false;
        }

        $rules = $this->getPreparedSets($defaultRules);

        if ($rules['all'] === true) {
            return true;
        }

        if (!array_key_exists($key, $rules)) {
            throw new InvalidArgumentException('Invalid rule key: '.$key);
        }

        return $rules[$key];
    }

    /**
     * Returns the prepared set level.
     *
     * Parameter: -s,--sets
     *
     * @param array<string, int|null> $defaultRuleLevels
     */
    public function getPreparedSetLevel(string $key, array $defaultRuleLevels = self::DEFAULT_PREPARED_SET_LEVELS): int|null
    {
        $ruleLevels = $this->getPreparedSetLevels($defaultRuleLevels);

        if (!array_key_exists($key, $ruleLevels)) {
            throw new InvalidArgumentException('Invalid rule level key: '.$key);
        }

        return $ruleLevels[$key];
    }

    /**
     * Returns the prepared set (active or not) or prepared set level.
     *
     * Parameter: -s,--sets
     *
     * @param array<string, bool> $defaultRules
     * @param array<string, int|null> $defaultRuleLevels
     */
    public function getPreparedSetOrPreparedSetLevel(
        string $key,
        array $defaultRules = self::DEFAULT_PREPARED_SETS,
        array $defaultRuleLevels = self::DEFAULT_PREPARED_SET_LEVELS
    ): int|bool
    {
        $ruleLevel = $this->getPreparedSetLevel($key, $defaultRuleLevels);

        if (!is_null($ruleLevel)) {
            return $ruleLevel;
        }

        return $this->getPreparedSet($key, $defaultRules);
    }

    /**
     * Returns all rules analyzed by rector.
     *
     * Parameter: -r,--rules
     *
     * @return string[]
     */
    public function getRules(array $default = []): array
    {
        return $this->rules ?? $default;
    }

    /**
     * Returns the wanted symfony version analyzed by rector.
     *
     * Parameter: --with-symfony
     */
    public function getWithSymfony(string|null $default = null): string|null
    {
        return $this->withSymfony ?? $default;
    }

    /**
     * Returns whether to analyze symfony code quality.
     *
     * Parameter: --with-symfony-code-quality
     */
    public function getWithSymfonyCodeQuality(bool $default = false): bool
    {
        return $this->withSymfonyCodeQuality ?? $default;
    }

    /**
     * Returns whether to show detailed information.
     *
     * Parameter: --with-symfony-constructor-injection
     */
    public function getWithSymfonyConstructorInjection(bool $default = false): bool
    {
        return $this->withSymfonyConstructorInjection ?? $default;
    }

    /**
     * Returns whether to show detailed information.
     *
     * Parameter: --details
     */
    public function isDetails(bool $default = false): bool
    {
        return $this->details ?? $default;
    }

    /**
     * Returns whether to run in dry mode.
     *
     * Parameter: --dry-run
     */
    public function isDryRun(bool $default = false): bool
    {
        return $this->dryRun ?? $default;
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

            foreach (self::ARGUMENT_MAPPING as $prefix => [$envKey, $hasValue, $removeArgument]) {
                $argumentName = '--'.$prefix.($hasValue ? '=' : '');
                if (str_starts_with($arg, $argumentName)) {
                    $value = $hasValue ? substr($arg, strlen($argumentName)) : '1';
                    $argumentKey = $prefix;

                    $this->arguments[$argumentKey] = $value;
                    putenv($envKey.'='.$value);

                    if ($removeArgument) {
                        unset($_SERVER['argv'][$i]);
                    }

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
        $type = $this->arguments['type'] ?? 'rector';

        foreach ($this->arguments as $key => $value) {
            $allowedTypes = self::ARGUMENT_MAPPING[$key][3] ?? [];
            if ($allowedTypes && !in_array($type, $allowedTypes, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument "%s" is not allowed in "%s" context.',
                    $key,
                    $type
                ));
            }

            switch ($key) {
                case 'include':
                    $this->adoptInclude($value);
                    break;
                case 'level':
                    $this->level = (int) $value;
                    break;
                case 'sets':
                    $this->adoptPreparedSets($value);
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

                case 'details':
                    $this->details = true;
                    break;
                case 'dry-run':
                    $this->dryRun = true;
                    break;

                case 'type':
                    break;

                default:
                    throw new InvalidArgumentException(sprintf('Invalid argument "%s" given.', $key));
            }
        }
    }

    /**
     * Adopt argument "include".
     */
    private function adoptInclude(string $value): void
    {
        $list = $this->splitList($value);

        $allowed = array_keys($this->getPathsIncluded());
        $invalid = array_diff($list, $allowed);

        if ($invalid !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid include keys: %s. Allowed keys: %s',
                    implode(', ', $invalid),
                    implode(', ', $allowed)
                ),
            );
        }

        $this->include = $list;
    }

    /**
     * Adopt argument "rules".
     */
    private function adoptPreparedSets(string $value): void
    {
        $list = $this->splitList($value);

        $allowed = array_keys(self::DEFAULT_PREPARED_SETS);

        $this->preparedSets = self::DEFAULT_PREPARED_SETS;
        $this->preparedSetLevels = self::DEFAULT_PREPARED_SET_LEVELS;

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
                if (!in_array($key, self::ALLOWED_PREPARED_SET_KEYS_WITH_LEVELS, true)) {
                    throw new InvalidArgumentException(
                        sprintf('Rule key "%s" does not accept levels (got "%s")', $key, $level)
                    );
                }

                $this->preparedSetLevels[$key] = (int) $level;
            } else {
                $this->preparedSets[$key] = true;
            }
        }
    }

    /**
     * Adopt argument "rules".
     */
    private function adoptRules(string $value): void
    {
        $list = $this->splitList($value);

        $allowed = array_keys($this->getRulesIncluded());
        $invalid = array_diff($list, $allowed);

        if ($invalid !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid rule keys: %s. Allowed keys: %s',
                    implode(', ', $invalid),
                    implode(', ', $allowed)
                ),
            );
        }

        $this->rules = $list;
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
}
