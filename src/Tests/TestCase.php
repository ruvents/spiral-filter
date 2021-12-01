<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Tests;

use Ruvents\SpiralFilter\Bootloader\AnnotatedFilterBootloader;
use Ruvents\SpiralFilter\Filter\FilterFactory;
use Ruvents\SpiralFilter\Filter\FilterInputFactory;
use Ruvents\SpiralFilter\Filter\FilterInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\Mapper\StdMapper;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\Transaction;
use Laminas\Hydrator\ObjectPropertyHydrator;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Ruvents\SpiralInput\Input\EntityMapper;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Composite\SelectiveReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Database\DatabaseProviderInterface;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Spiral\Database\Schema\AbstractTable;
use Spiral\Validation\Checker\NumberChecker;
use Spiral\Validation\Checker\TypeChecker;
use Spiral\Validation\Config\ValidatorConfig;

/**
 * @internal
 */
class TestCase extends BaseTestCase
{
    public const VALIDATION_CONFIG = [
        'checkers' => [
            'type' => TypeChecker::class,
            'number' => NumberChecker::class,
        ],
    ];

    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->container->bindInjector(FilterInterface::class, AnnotatedFilterBootloader::class);

        $this->container->bindSingleton(
            ValidatorConfig::class,
            new ValidatorConfig(static::VALIDATION_CONFIG)
        );
        $this->container->bindSingleton(ReaderInterface::class, new SelectiveReader([
            new AnnotationReader(), new AttributeReader(),
        ]));

        $this->initORM();

        $factory = $this->container->get(FactoryInterface::class);
        $this->container->bindSingleton(EntityMapper::class, $factory->make(
            EntityMapper::class,
            [
                'hydrator' => new ObjectPropertyHydrator(),
                'extractor' => new ObjectPropertyHydrator(),
            ]
        ));
        $this->container->bindSingleton(FilterInputFactory::class, $factory->make(
            FilterInputFactory::class,
            ['hydrator' => new ObjectPropertyHydrator()]
        ));
    }

    protected function getFactory(): FilterFactory
    {
        return $this->container->get(FilterFactory::class);
    }

    protected function initORM(): void
    {
        $dbal = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => [
                'default' => [
                    'driver' => 'memory',
                ],
            ],
            'drivers' => [
                'memory' => [
                    'driver' => SQLiteDriver::class,
                    'options' => [
                        'connection' => 'sqlite::memory:',
                    ],
                ],
            ],
        ]));
        $this->container->bindSingleton(DatabaseProviderInterface::class, $dbal);

        $orm = new ORM(
            new Factory($dbal),
            new Schema([
                'User' => [
                    Schema::MAPPER => StdMapper::class,
                    Schema::DATABASE => 'default',
                    Schema::TABLE => 'users',
                    Schema::PRIMARY_KEY => 'id',
                    Schema::COLUMNS => [
                        'id' => 'id',
                        'username' => 'username',
                    ],
                    Schema::TYPECAST => [
                        'id' => 'int',
                    ],
                    Schema::RELATIONS => [],
                ],
            ]),
        );

        $this->container->bindSingleton(ORMInterface::class, $orm);

        /** @var AbstractTable $users */
        $users = $dbal->database()->table('users')->getSchema();
        $users->primary('id');
        $users->string('username');
        $users->save();

        $t = $this->container->get(Transaction::class);
        $t->persist($orm->make('User', ['id' => 1, 'username' => 'User1']));
        $t->persist($orm->make('User', ['id' => 123, 'username' => 'User123']));
        $t->run();
    }
}
