<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Filter\FilterExtractor;
use Ruvents\SpiralFilter\Tests\Fixtures\Filter;

/**
 * @internal
 */
final class FilterExtractorTest extends TestCase
{
    public function testExtraction(): void
    {
        /** @var FilterExtractor */
        $extractor = $this->container->get(FilterExtractor::class);

        $class = new class() {
            public function testAction(int $someArgument, Filter $someFilter): void
            {
            }
        };

        $this->assertSame(
            ['someFilter' => Filter::class],
            $extractor->extract(\get_class($class), 'testAction')
        );
    }
}
