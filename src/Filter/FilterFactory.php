<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Filter;

use Ruvents\SpiralFilter\Exception\ValidationException;
use Ruvents\SpiralFilter\Validation\FilterValidator;
use Ruvents\SpiralInput\Input\EntityMapper;
use Ruvents\SpiralInput\Input\InputMapper;
use Spiral\Filters\InputInterface;

final class FilterFactory
{
    public function __construct(
        private FilterInputFactory $filterInputFactory,
        private InputMapper $inputMapper,
        private FilterValidator $filterValidator,
        private EntityMapper $entityMapper
    ) {
    }

    /**
     * @param class-string $class
     *
     * @throws ValidationException
     */
    public function make(
        string $class,
        InputInterface $input,
        mixed $context = null,
        bool $ignoreEmptyFields = false,
    ): FilterInterface {
        $filter = new $class();

        if (false === $filter instanceof FilterInterface) {
            throw new \InvalidArgumentException(
                sprintf('Class %s does not implement FilterInterface', $class)
            );
        }

        $filterInput = $this->filterInputFactory->make($class, $input);
        $validator = $this->filterValidator->validate(
            $class,
            $filterInput,
            $context,
            $ignoreEmptyFields,
        );

        if (false === $validator->isValid()) {
            throw new ValidationException($validator->getErrors());
        }

        $filter = $this->inputMapper->map(
            $this->entityMapper->map($filter, $input),
            $filterInput
        );

        return $filter;
    }
}
