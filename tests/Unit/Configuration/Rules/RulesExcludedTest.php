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

namespace Ixnode\PhpQualitySuite\Tests\Unit\Configuration\Rules;

use InvalidArgumentException;
use Ixnode\PhpQualitySuite\Configuration\Rules\RulesExcluded;
use PHPUnit\Framework\TestCase;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Throwable;

/**
 * Class RulesExcludedTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2025-09-25)
 * @since 1.0.0 (2025-09-25) First version
 * @link RulesExcluded
 */
final class RulesExcludedTest extends TestCase
{
    /**
     * @dataProvider dataProviderExcluded
     * @test
     *
     * @param string[] $data
     * @param string[] $expected
     * @param class-string<Throwable>|null $exception
     */
    public function excluded(float|null $phpVersion, float|null $symfonyVersion, array $data, array $expected, string|null $exception = null): void
    {
        /* Arrange */
        if (!is_null($exception)) {
            $this->expectException($exception);
        }

        /* Act */
        $rulesExcluded = new RulesExcluded($data);

        /* Assert */
        $this->assertEquals($expected, $rulesExcluded->get(phpVersion: $phpVersion, symfonyVersion: $symfonyVersion));
    }

    public static function dataProviderExcluded(): iterable
    {
        /* Empty test. */
        yield 'Empty test' => [
            null,
            null,
            [],
            []
        ];

        /* Simple test. */
        yield 'Simple test 1' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class,
            ],
            [
                VarToPublicPropertyRector::class,
            ]
        ];
        yield 'Simple test 2' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
            ]
        ];
        yield 'Simple test (duplicate)' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class,
                VarToPublicPropertyRector::class,
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                NullCoalescingOperatorRector::class,
                NullCoalescingOperatorRector::class,
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
            ]
        ];

        /* None exception. */
        yield 'None exception: with tagging php (php tagged rules even without php version)' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':php',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None exception: with tagging symfony (symfony tagged rules only with symfony version)' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':symfony',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];

        /* With exception. */
        yield 'With exception: with tagging php1 (unsupported tag)' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':php1',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            InvalidArgumentException::class
        ];
        yield 'With exception: with tagging php1 (unsupported tag) and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':php1<=8.0',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            InvalidArgumentException::class
        ];
        yield 'With exception: with tagging symfony2 (unsupported tag)' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':symfony2',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            InvalidArgumentException::class
        ];
        yield 'With exception: with tagging symfony2 (unsupported tag) and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':symfony2<=6.4',
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            InvalidArgumentException::class
        ];

        /* None given PHP and Symfony version. */
        yield 'None given PHP and Symfony version: without tagging' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with tagging php' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':php',
                NullCoalescingOperatorRector::class.':php',
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with tagging symfony' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':symfony',
                NullCoalescingOperatorRector::class.':symfony',
                ClosureToArrowFunctionRector::class
            ],
            [
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with empty tagging (php) and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with tagging php and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with tagging symfony and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':symfony<=6.4',
                NullCoalescingOperatorRector::class.':symfony>6.4',
                ClosureToArrowFunctionRector::class
            ],
            [
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'None given PHP and Symfony version: with tagging symfony/php and version' => [
            null,
            null,
            [
                VarToPublicPropertyRector::class.':php<=8.0',
                NullCoalescingOperatorRector::class.':symfony>6.4',
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];

        /* With a given PHP version. */
        yield 'With a given PHP 8.0 version' => [
            8.0,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class
            ],
            [
                VarToPublicPropertyRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'With a given PHP 8.0 version 2' => [
            8.0,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
            ],
            [
                VarToPublicPropertyRector::class,
                ClosureToArrowFunctionRector::class
            ]
        ];
        yield 'With a given PHP 8.1 version' => [
            8.1,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
            ],
            [
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given PHP 8.1 version 2' => [
            8.1,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
            ],
            [
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given PHP 8.2 version' => [
            8.2,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given PHP 8.2 version 2' => [
            8.2,
            null,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given PHP 8.2 version 2 (symfony)' => [
            8.2,
            null,
            [
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                ClosureToArrowFunctionRector::class,
            ]
        ];

        /* With a given Symfony version. */
        yield 'With a given Symfony 6.4 version' => [
            null,
            6.4,
            [
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
                ClosureToArrowFunctionRector::class,
            ],
            [
                RenameMethodRector::class,
                RenameClassRector::class,
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given Symfony 7.0 version' => [
            null,
            7.0,
            [
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
                ClosureToArrowFunctionRector::class,
            ],
            [
                ArgumentAdderRector::class,
                RenameClassRector::class,
                ClosureToArrowFunctionRector::class,
            ]
        ];
        yield 'With a given Symfony 7.1 version' => [
            null,
            7.1,
            [
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
                ClosureToArrowFunctionRector::class,
            ],
            [
                ArgumentAdderRector::class,
                RenameClassRector::class,
                ClosureToArrowFunctionRector::class,
            ]
        ];

        /* With a given PHP and Symfony version. */
        yield 'With a given PHP 8.0 and Symfony 6.4 version' => [
            8.0,
            6.4,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                VarToPublicPropertyRector::class,
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class,
                RenameClassRector::class
            ]
        ];
        yield 'With a given PHP 8.1 and Symfony 6.4 version' => [
            8.1,
            6.4,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class,
                RenameClassRector::class
            ]
        ];
        yield 'With a given PHP 8.2 and Symfony 6.4 version' => [
            8.2,
            6.4,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class,
                RenameClassRector::class
            ]
        ];
        yield 'With a given PHP 8.2 and Symfony 7.0 version' => [
            8.2,
            7.0,
            [
                VarToPublicPropertyRector::class.':<=8.0',
                NullCoalescingOperatorRector::class.':>8.1',
                ClosureToArrowFunctionRector::class,
                RenameMethodRector::class.':symfony<=6.4',
                ArgumentAdderRector::class.':symfony>6.4',
                RenameClassRector::class.':symfony',
            ],
            [
                NullCoalescingOperatorRector::class,
                ClosureToArrowFunctionRector::class,
                ArgumentAdderRector::class,
                RenameClassRector::class
            ]
        ];
    }
}
