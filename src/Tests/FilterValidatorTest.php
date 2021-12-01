<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Filter\FilterInput;
use Ruvents\SpiralFilter\Tests\Fixtures\ValidatedFilter;
use Ruvents\SpiralFilter\Validation\FilterValidator;

/**
 * @internal
 */
class FilterValidatorTest extends TestCase
{
    public function testValidation(): void
    {
        /** @var FilterValidator */
        $validationProvider = $this->container->get(FilterValidator::class);
        $validator = $validationProvider->validate(
            ValidatedFilter::class,
            new FilterInput([
                'array' => [
                    'some' => [
                        'user' => [
                            'id' => 1,
                            'username' => 'Tester',
                        ],
                    ],
                ],
            ])
        );
        $this->assertTrue($validator->isValid());

        $validator = $validationProvider->validate(
            ValidatedFilter::class,
            new FilterInput([])
        );
        $this->assertFalse($validator->isValid());
        $this->assertSame(
            [
                'array' => [
                    'some' => [
                        'user' => [
                            'id' => 'Oh no',
                            'username' => 'Username is empty',
                        ],
                    ],
                ],
            ],
            $validator->getErrors()
        );
    }

    public function testValidationIgnoreEmptyFields(): void
    {
        /** @var FilterValidator */
        $validationProvider = $this->container->get(FilterValidator::class);
        $validator = $validationProvider->validate(
            ValidatedFilter::class,
            new FilterInput(['array' => ['some' => ['user' => ['id' => 1]]]]),
            null,
            true
        );
        $this->assertTrue($validator->isValid());

        $validator = $validationProvider->validate(
            ValidatedFilter::class,
            new FilterInput(['array' => ['some' => ['user' => ['id' => -1]]]]),
            null,
            true
        );
        $this->assertFalse($validator->isValid());
    }
}
