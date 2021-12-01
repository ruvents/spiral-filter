<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Filter;

use Ruvents\SpiralPropertyAccessor\PropertyAccessor;
use Spiral\Filters\InputInterface;

final class FilterInput implements InputInterface
{
    private array $data;

    private PropertyAccessor $accessor;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->accessor = new PropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function withPrefix(string $prefix, bool $add = true): InputInterface
    {
        // TODO: реализовать правильно.
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(string $source, string $name = null)
    {
        return $this->accessor->get($this->data, $source.'.'.$name);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
