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

namespace Ixnode\PhpQualitySuite\Rector;

/**
 * Class RectorConfigPrinterResult
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-28)
 * @since 1.0.0 (2025-09-28) First version
 */
final class RectorConfigPrinterResult
{
    /**
     */
    public function __construct(private int $changedFiles, private int $changeableFiles)
    {
    }

    /**
     * Print result.
     */
    public function print(): void
    {
        echo PHP_EOL;
        echo str_repeat('=', RectorConfigPrinter::LENGTH_SEPARATOR).PHP_EOL;

        switch (true) {
            case $this->changedFiles <= 0 && $this->changeableFiles <= 0:
                echo "No file changeable or changed.".PHP_EOL;
                break;

            case $this->changedFiles > 0:
                echo match (true) {
                        $this->changedFiles === 1 => sprintf('Total %d file changed.', $this->changedFiles),
                        default => sprintf('Total %d files changed.', $this->changedFiles),
                    }.PHP_EOL;
                break;

            case $this->changeableFiles > 0:
                echo match (true) {
                        $this->changeableFiles === 1 => sprintf('Total %d file changeable.', $this->changeableFiles),
                        default => sprintf('Total %d files changeable.', $this->changeableFiles),
                    }.PHP_EOL;
                break;
        }

        echo str_repeat('=', RectorConfigPrinter::LENGTH_SEPARATOR).PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }
}
