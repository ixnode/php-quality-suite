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

use Ixnode\PhpQualitySuite\RectorConfigBuilder;
use Rector\Configuration\RectorConfigBuilder as RectorConfigBuilderVendor;
use Rector\Exception\Configuration\InvalidConfigurationException;

/**
 * Use the PHP version of composer.json: `require['php']` -> `^8.0`
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-20)
 * @since 1.0.0 (2025-09-20) First version
 * @example Only critical: vendor/bin/rector process --config=rector/bootstrap.php --dry-run --level=0 --include=src
 * @example Some extra rules: vendor/bin/rector process --config=rector/bootstrap.php --dry-run --level=0 --include=src --rules=deadCode:1,codeQuality,instanceOf
 * @example Some extra rules: vendor/bin/rector process --config=rector/bootstrap.php --dry-run --level=0 --include=src --rules=all,deadCode:1
 */

final class RectorBootstrap
{
    private RectorConfigBuilder $rectorConfigBuilder;

    public function __construct()
    {
        $this->rectorConfigBuilder = new RectorConfigBuilder();
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function run(): RectorConfigBuilderVendor
    {
        $this->rectorConfigBuilder->printSetup();

        return $this->rectorConfigBuilder->getRectorConfigBuilder();
    }
}

return (new RectorBootstrap())->run();
