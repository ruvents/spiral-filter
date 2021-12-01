<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests\Fixtures;

use Ruvents\SpiralFilter\Filter\FilterInterface;
use Ruvents\SpiralInput\Annotation\From;
use Ruvents\SpiralInput\Annotation\HydrateFromEntity;

#[HydrateFromEntity(entity: 'User', from: 'array:userId')]
final class EntityMappedFilter implements FilterInterface
{
    /**
     * @From("array:userId")
     */
    public ?int $userId = null;

    /**
     * @From("array:username")
     */
    public ?string $username = null;

    public ?int $notMappedProperty = null;
}
