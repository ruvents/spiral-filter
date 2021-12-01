<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Filter\FilterInput;

/**
 * @internal
 */
class FilterInputTest extends TestCase
{
    public function testGetValue(): void
    {
        $input = new FilterInput([
            'query' => [
                'some' => 'value',
            ],
            'body' => [
                'foo' => 123,
            ],
        ]);

        $this->assertSame('value', $input->getValue('query', 'some'));
        $this->assertSame(123, $input->getValue('body', 'foo'));
    }

    public function testGetData(): void
    {
        $input = new FilterInput($data = [
            'query' => [
                'some' => 'value',
            ],
            'body' => [
                'foo' => 123,
            ],
        ]);

        $this->assertSame($data, $input->getData());
    }
}
