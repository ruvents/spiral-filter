<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Bootloader;

use Ruvents\SpiralFilter\Filter\FilterFactory;
use Ruvents\SpiralFilter\Filter\FilterInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\InputInterface;

final class AnnotatedFilterBootloader extends Bootloader implements InjectorInterface
{
    protected const SINGLETONS = [
        InputInterface::class => InputScope::class,
    ];

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        $this->container->bindInjector(FilterInterface::class, self::class);
    }

    public function createInjection(\ReflectionClass $class, string $context = null): FilterInterface
    {
        return $this->container->get(FilterFactory::class)->make(
            $class->getName(),
            $this->container->get(InputInterface::class)
        );
    }
}
