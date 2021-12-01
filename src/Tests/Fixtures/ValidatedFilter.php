<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests\Fixtures;

use Ruvents\SpiralFilter\Filter\FilterInterface;
use Ruvents\SpiralValidation\Annotation as Assert;
use Ruvents\SpiralInput\Annotation\From;

final class ValidatedFilter implements FilterInterface
{
    /**
     * @From("array:some.user.id")
     * @Assert\Type\NotNull(message="Oh no")
     * @Assert\Number\Higher(0)
     */
    public ?int $id = null;

    /**
     * @From("array:some.user.username")
     * @Assert\Type\NotNull(message="Username is empty")
     */
    public ?string $username = null;
}
