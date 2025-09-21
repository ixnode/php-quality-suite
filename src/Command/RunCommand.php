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

namespace Ixnode\PhpQualitySuite\Command;

use Ahc\Cli\Input\Command;
use Exception;
use RuntimeException;

/**
 * Class RunCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 * @property string|null $path
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RunCommand extends Command
{
    /**
     * @example bin/php-quality-suite analyze --level=20 --rules=all,deadCode:1 --details --dry-run --include=src
     */
    public function __construct()
    {
        parent::__construct('analyze', 'Run the rector command and analyze code.');

        $this
            ->option('-p,--rules [rules]', '', 'strval', '')
            ->option('-i,--include [include]', '', 'strval', '')
            ->option('-l,--level [level]', '', 'intval', '')
            ->option('-d,--details', '', 'boolval', '')
            ->option('-r,--dry-run', '', 'boolval', '')
        ;
    }

    /**
     * Executes the RunCommand.
     *
     * @throws Exception
     */
    public function execute(): int
    {
        $forwardArgs = array_slice($_SERVER['argv'], 2);

        $cmd = array_merge(
            ['composer', 'run', 'analyze'],
            ['--'],
            $forwardArgs
        );

        $process = proc_open(
            $cmd,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to run composer');
        }

        return proc_close($process);
    }
}
