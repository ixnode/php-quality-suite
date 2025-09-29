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
use Ixnode\PhpQualitySuite\PhpStan\PhpStanBootstrap;

/**
 * Class RunRectorCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 * @property string|null $rules
 * @property string|null $include
 * @property int|null $level
 * @property bool|null $details
 * @property bool|null $dryRun
 * @property string|null $withSymfony
 * @property bool|null $withSymfonyCodeQuality
 * @property bool|null $withSymfonyConstructorInjection
 */
class RunPhpStanCommand extends Command
{
    /**
     * @example bin/php-quality-suite phpstan --include=src,tests --level=2
     */
    public function __construct()
    {
        parent::__construct('phpstan', 'Run the PHPStan command and analyze code.');

        $this
            ->option('-i,--include [include]', '', 'strval', '')
            ->option('-l,--level [level]', '', 'intval', '')

            ->option('--details', '', 'boolval', '')
            ->option('--dry-run', '', 'boolval', '')
        ;
    }

    /**
     * Executes the RunCommand.
     *
     * @throws Exception
     */
    public function execute(): int
    {
        $phpStanBootstrap = new PhpStanBootstrap();

        $parameters = $phpStanBootstrap->getParameters();

        $cmd = array_merge(
            ['php', 'vendor/bin/phpstan', 'analyse'],
            $parameters
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

        return proc_close($process);
    }
}
