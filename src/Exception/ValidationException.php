<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Exception;

final class ValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->message = 'Filter did not pass validation.';
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
