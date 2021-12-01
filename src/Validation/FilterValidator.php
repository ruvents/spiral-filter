<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Validation;

use Ruvents\SpiralFilter\Filter\FilterInput;
use Ruvents\SpiralFilter\Traits\FromAttributesTrait;
use Ruvents\SpiralValidation\Validation\ValidationProvider;
use Ruvents\SpiralValidation\Validation\Validator;
use Ruvents\SpiralPropertyAccessor\PropertyAccessor;
use Spiral\Attributes\ReaderInterface;
use Spiral\Validation\ValidatorInterface;

final class FilterValidator
{
    use FromAttributesTrait;

    private array $rulesCache = [];

    public function __construct(
        private ReaderInterface $reader,
        private ValidationProvider $validationProvider,
        private PropertyAccessor $propertyAccessor,
    ) {
    }

    /**
     * @param class-string $filterClass
     */
    public function validate(
        string $filterClass,
        FilterInput $input,
        mixed $context = null,
        bool $ignoreEmptyFields = false,
    ): ValidatorInterface {
        $data = $input->getData();

        return new Validator(
            $data,
            $this->buildRules($filterClass, $ignoreEmptyFields ? $data : null),
            $this->validationProvider,
            $context
        );
    }

    /**
     * TODO: тест.
     *
     * @param class-string $filterClass
     */
    public function buildRules(string $filterClass, ?array $data = null): array
    {
        if (null === $data && \array_key_exists($filterClass, $this->rulesCache)) {
            return $this->rulesCache[$filterClass];
        }

        $result = [];
        $rules = $this->validationProvider->getAnnotations($filterClass);

        foreach ($this->getFromAnnotations($filterClass) as $property => $from) {
            if (false === \array_key_exists($property, $rules)) {
                continue;
            }

            $path = str_replace(':', '.', $from->source);

            if (null !== $data && false === $this->propertyAccessor->has($data, $path)) {
                continue;
            }

            $result[$path] = $rules[$property] ?? [];
        }

        if (null === $data) {
            return $this->rulesCache[$filterClass] = $result;
        }

        return $result;
    }
}
