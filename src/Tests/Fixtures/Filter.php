<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests\Fixtures;

use Ruvents\SpiralFilter\Filter\FilterInterface;
use Ruvents\SpiralInput\Annotation\From;

final class Filter implements FilterInterface
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
