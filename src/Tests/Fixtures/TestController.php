<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests\Fixtures;

final class TestController
{
    public function filter(Filter $filter): array
    {
        return [
            'userId' => $filter->userId,
            'username' => $filter->username,
        ];
    }

    public function filterWithValidation(ValidatedFilter $filter): array
    {
        return [
            'userId' => $filter->id,
            'username' => $filter->username,
        ];
    }
}
