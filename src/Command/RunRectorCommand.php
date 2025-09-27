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
use Ixnode\PhpQualitySuite\Rector\RectorConfigPrinterResult;
use RuntimeException;

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
class RunRectorCommand extends Command
{
    /**
     * @example bin/php-quality-suite rector --level=20 --sets=all,deadCode:1 --details --dry-run --include=src
     */
    public function __construct()
    {
        parent::__construct('rector', 'Run the rector command and analyze code.');

        $this
            ->option('-i,--include [include]', '', 'strval', '')
            ->option('-l,--level [level]', '', 'intval', '')
            ->option('-s,--sets [sets]', '', 'strval', '')
            ->option('-r,--rules [rules]', '', 'strval', '')
            ->option('-n,--names [names]', '', 'strval', '')

            ->option('--with-symfony', '', 'strval', '')
            ->option('--with-symfony-code-quality', '', 'boolval', '')
            ->option('--with-symfony-constructor-injection', '', 'boolval', '')

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
        $forwardArgs = array_slice($_SERVER['argv'], 2);

        $cmd = array_merge(
            ['php', 'vendor/bin/rector', 'process', '--ansi', '--config='.__DIR__.'/../../rector/bootstrap.php', '--type=rector'],
            $forwardArgs
        );

        $process = proc_open(
            $cmd,
            [
                0 => STDIN,
                1 => ['pipe', 'w'],
                2 => STDERR,
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to run vendor/bin/rector');
        }

        $changedFiles = 0;
        $changeableFiles = 0;

        while (!feof($pipes[1])) {
            $current = fread($pipes[1], 8192);
            echo $current;

            if (preg_match('/\[(OK)] (\d+) (file|files) has been changed/', $current, $m)) {
                $changedFiles += (int) $m[2];
            }

            if (preg_match('/\[(OK)] (\d+) (file|files) would have been changed/', $current, $m)) {
                $changeableFiles += (int) $m[2];
            }
        }

        fclose($pipes[1]);

        (new RectorConfigPrinterResult(
            changedFiles: $changedFiles,
            changeableFiles:  $changeableFiles,
        ))->print();

        return proc_close($process);
    }
}
