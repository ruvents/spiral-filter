<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Traits;

use Ruvents\SpiralInput\Annotation\From;
use Spiral\Attributes\ReaderInterface;

trait FromAttributesTrait
{
    private array $fromCache = [];

    private ReaderInterface $reader;

    /**
     * @param class-string $filterClass
     *
     * @return From[]
     */
    private function getFromAnnotations(string $filterClass): \Generator
    {
        if (isset($this->fromCache[$filterClass])) {
            yield from $this->fromCache[$filterClass];

            return;
        }

        $this->fromCache[$filterClass] = [];
        $class = new \ReflectionClass($filterClass);

        /** @var \ReflectionProperty $property */
        foreach ($class->getProperties() as $property) {
            $metadata = $this->reader->firstPropertyMetadata($property, From::class);

            if (null === $metadata) {
                continue;
            }

            $propertyName = $property->getName();
            $this->fromCache[$filterClass][$propertyName] = $metadata;

            yield $propertyName => $metadata;
        }
    }
}
