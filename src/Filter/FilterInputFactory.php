<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Filter;

use Ruvents\SpiralFilter\Traits\FromAttributesTrait;
use Ruvents\SpiralPropertyAccessor\PropertyAccessor;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Ruvents\SpiralInput\Exception\EntityNotFoundException;
use Ruvents\SpiralInput\Input\EntityMapper;
use Ruvents\SpiralInput\Input\InputMapper;
use Spiral\Attributes\ReaderInterface;
use Spiral\Filters\InputInterface;

final class FilterInputFactory
{
    use FromAttributesTrait;

    private InputMapper $inputMapper;
    private EntityMapper $entityMapper;
    private PropertyAccessor $accessor;
    private ReaderInterface $reader;
    private HydratorInterface $hydrator;

    public function __construct(
        InputMapper $inputMapper,
        EntityMapper $entityMapper,
        PropertyAccessor $accessor,
        ReaderInterface $reader,
        HydratorInterface $hydrator = null
    ) {
        $this->inputMapper = $inputMapper;
        $this->entityMapper = $entityMapper;
        $this->accessor = $accessor;
        $this->reader = $reader;
        $this->hydrator = $hydrator ?? new ReflectionHydrator();
    }

    /**
     * @param class-string $filterClass
     */
    public function make(string $filterClass, InputInterface $input, bool $withEntityData = false): FilterInput
    {
        $result = [];
        $data = $this->inputMapper->extract($filterClass, $input, false);
        $entityData = $withEntityData ? $this->getEntityData($filterClass, $input) : [];

        foreach ($this->getFromAnnotations($filterClass) as $property => $from) {
            $path = str_replace(':', '.', $from->source);

            if ($this->accessor->has($data, $property)) {
                $this->accessor->set($result, $path, $this->accessor->get($data, $property));
            } elseif (\array_key_exists($property, $entityData)) {
                $this->accessor->set($result, $path, $entityData[$property]);
            }
        }

        return new FilterInput($result);
    }

    /**
     * @param class-string $filterClass
     */
    private function getEntityData(string $filterClass, InputInterface $input): array
    {
        if (null === $attribute = $this->entityMapper->getAttribute($filterClass)) {
            return [];
        }

        if (null === $value = $this->entityMapper->getValue($attribute, $input)) {
            return [];
        }

        if (null === $entity = $this->entityMapper->getEntity($attribute, $value)) {
            throw new EntityNotFoundException($attribute->entity, $attribute->loadBy, (string) $value);
        }

        return $this->hydrator->extract($entity);
    }
}
