<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Domain\FilterInterceptor;
use Ruvents\SpiralFilter\Tests\Fixtures\TestController;
use Http\Factory\Guzzle\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Core;
use Spiral\Core\InterceptableCore;
use Spiral\Filters\ArrayInput;

/**
 * @internal
 */
class FilterInterceptorTest extends TestCase
{
    public function testInterceptor(): void
    {
        $inputData = [
            'userId' => 1,
            'username' => 'some user',
        ];

        $core = new InterceptableCore(new Core($this->container));
        $core->addInterceptor(
            $this->container->make(
                FilterInterceptor::class,
                [
                    'input' => new ArrayInput($inputData),
                ]
            )
        );

        $this->assertSame(
            $inputData,
            $this->container->runScope(
                [
                    ServerRequestInterface::class => (new ServerRequestFactory())
                        ->createServerRequest('POST', '/test'),
                ],
                fn () => $core->callAction(TestController::class, 'filter')
            )
        );
    }

    public function testInterceptorWithValidation(): void
    {
        $inputData = [];

        $core = new InterceptableCore(new Core($this->container));
        $core->addInterceptor(
            $this->container->make(
                FilterInterceptor::class,
                [
                    'input' => new ArrayInput($inputData),
                ]
            )
        );

        $this->assertSame(
            [
                'status' => 400,
                'data' => [
                    'some' => [
                        'user' => [
                            'id' => 'Oh no',
                            'username' => 'Username is empty',
                        ],
                    ],
                ],
            ],
            $this->container->runScope(
                [
                    ServerRequestInterface::class => (new ServerRequestFactory())
                        ->createServerRequest('POST', '/test'),
                ],
                fn () => $core->callAction(TestController::class, 'filterWithValidation')
            )
        );
    }

    public function testInterceptorPatchRequest(): void
    {
        $inputData = [
            'some' => [
                'user' => [
                    'username' => 'some user',
                ],
            ],
        ];

        $core = new InterceptableCore(new Core($this->container));
        $core->addInterceptor(
            $this->container->make(
                FilterInterceptor::class,
                [
                    'input' => new ArrayInput($inputData),
                ]
            )
        );

        $this->assertSame(
            [
                'userId' => null,
                'username' => 'some user',
            ],
            $this->container->runScope(
                [
                    ServerRequestInterface::class => (new ServerRequestFactory())
                        ->createServerRequest('PATCH', '/test'),
                ],
                fn () => $core->callAction(TestController::class, 'filterWithValidation')
            )
        );
    }
}
