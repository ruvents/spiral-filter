<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Tests\Fixtures\EntityMappedFilter;
use Ruvents\SpiralFilter\Tests\Fixtures\Filter;
use Spiral\Core\Container\Autowire;
use Spiral\Filters\ArrayInput;
use Spiral\Filters\InputInterface;

/**
 * @internal
 */
class FilterFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->container->bindSingleton(
            InputInterface::class,
            new Autowire(ArrayInput::class, [
                'data' => ['userId' => 1],
            ])
        );
    }

    public function testInjection(): void
    {
        $filter = $this->container->get(Filter::class);

        $this->assertSame(1, $filter->userId);
    }

    public function testFilterFactory(): void
    {
        /* @var Filter */
        $filter = $this->getFactory()->make(
            Filter::class,
            new ArrayInput(['userId' => 123])
        );

        $this->assertSame(123, $filter->userId);
    }

    public function testFilterHydratedFromEntity(): void
    {
        $filter = $this->getFactory()->make(
            EntityMappedFilter::class,
            new ArrayInput(['userId' => 1])
        );

        $this->assertSame(1, $filter->userId);
        $this->assertSame('User1', $filter->username);
    }

    public function testFilterHydratedFromEntityWithOverwrittenValues(): void
    {
        $filter = $this->getFactory()->make(
            EntityMappedFilter::class,
            new ArrayInput(['userId' => 1, 'username' => 'Overwritten!'])
        );

        $this->assertSame(1, $filter->userId);
        $this->assertSame('Overwritten!', $filter->username);
    }
}
