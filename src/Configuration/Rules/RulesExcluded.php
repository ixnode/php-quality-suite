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

use InvalidArgumentException;
use Ixnode\PhpQualitySuite\Configuration\PqsConfiguration;
use Ixnode\PhpQualitySuite\Tests\Unit\Configuration\Rules\RulesExcludedTest;

/**
 * Class RulesExcluded
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-25)
 * @since 1.0.0 (2025-09-25) First version
 * @link RulesExcludedTest
 */
final class RulesExcluded
{
    /** @var string[] */
    private array $rulesExcluded;

    /**
     * @param PqsConfiguration|string[] $configuration
     */
    public function __construct(PqsConfiguration|array $configuration)
    {
        $this->rulesExcluded = match (true) {
            $configuration instanceof PqsConfiguration => $configuration->getRulesExcluded(),
            default => $configuration,
        };
    }

    /**
     * @return string[]
     */
    public function get(float|null $phpVersion = null, float|null $symfonyVersion = null): array
    {
        $rules = $this->rulesExcluded;

        /**
         * Shortcut: if both are zero → return all rules but check constraints
         */
        if ($phpVersion === null && $symfonyVersion === null) {
            return array_values(array_unique(array_filter(array_map(
                static function (string $rule): ?string {
                    [$class, $constraint] = array_pad(explode(':', $rule, 2), 2, null);

                    if ($constraint === null || $constraint === '') {
                        return $class;
                    }

                    $constraint = trim($constraint);

                    /* Only prefix without version specification. */
                    if ($constraint === 'php') {
                        return $class;
                    }

                    if ($constraint === 'symfony') {
                        // $symfonyVersion === null → filter out.
                        return null;
                    }

                    /* Prefix + Version. */
                    if (preg_match('/^(php|symfony)?(?:\s*(=|>=|<=|>|<|<<)\s*([\d.]+))$/', $constraint, $m)) {
                        $prefix = $m[1] ?: 'php';

                        if ($prefix === 'symfony') {
                            // $symfonyVersion === null → filter out.
                            return null;
                        }

                        return $class;
                    }

                    throw new InvalidArgumentException("Invalid version constraint: $constraint");
                },
                $rules
            ))));
        }

        return array_values(array_unique(array_filter(array_map(
            static function (string $rule) use ($phpVersion, $symfonyVersion): ?string {

                [$class, $constraint] = array_pad(explode(':', $rule, 2), 2, null);

                /* No constraint → always valid */
                if ($constraint === null || $constraint === '') {
                    return $class;
                }

                $constraint = trim($constraint);

                /* Only prefix without version specification. */
                if ($constraint === 'php' || $constraint === 'symfony') {
                    return match ($constraint) {
                        'php'     => $phpVersion     === null ? null : $class,
                        'symfony' => $symfonyVersion === null ? null : $class,
                        default   => throw new InvalidArgumentException("Invalid version constraint prefix: $constraint"),
                    };
                }

                /* Prefix + version (php or symfony, with operator). */
                if (preg_match('/^(php|symfony)?(?:\s*(=|>=|<=|>|<|<<)\s*([\d.]+))$/', $constraint, $m)) {
                    $prefix = $m[1] ?: 'php'; // default: php
                    $op     = $m[2] ?: '=';
                    $target = (float) $m[3];

                    return match ($prefix) {
                        'php' => $phpVersion === null
                            ? null
                            : (match ($op) {
                                '='  => $phpVersion == $target,
                                '>'  => $phpVersion >  $target,
                                '>=' => $phpVersion >= $target,
                                '<'  => $phpVersion <  $target,
                                '<=' => $phpVersion <= $target,
                                default => false,
                            } ? $class : null),
                        'symfony' => $symfonyVersion === null
                            ? null
                            : (match ($op) {
                                '='  => $symfonyVersion == $target,
                                '>'  => $symfonyVersion >  $target,
                                '>=' => $symfonyVersion >= $target,
                                '<'  => $symfonyVersion <  $target,
                                '<=' => $symfonyVersion <= $target,
                                default => false,
                            } ? $class : null),
                        default => throw new InvalidArgumentException("Invalid version constraint prefix: $prefix"),
                    };
                }

                /* Anything else is invalid. */
                throw new InvalidArgumentException("Invalid version constraint: $constraint");
            },
            $rules
        ))));
    }
}
