<?php

declare(strict_types=1);

namespace Ruvents\SpiralFilter\Filter;

use Psr\SimpleCache\CacheInterface;

final class FilterExtractor
{
    public function __construct(private ?CacheInterface $cache = null)
    {
    }

    /**
     * Извлекет из данного action'а данные о параметрах-фильтрах.
     *
     * @param class-string $controller
     *
     * @return array<string, class-string>
     */
    public function extract(string $controller, string $action): array
    {
        $cacheKey = sprintf('%s:%s:%s', __CLASS__, $controller, $action);

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $result = [];

        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            $parameterClass = $this->getParameterClass($parameter);

            if (null === $parameterClass) {
                continue;
            }

            if ($parameterClass->implementsInterface(FilterInterface::class)) {
                $result[$parameter->getName()] = $parameterClass->getName();
            }
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $result);
        }

        return $result;
    }

    private function getParameterClass(\ReflectionParameter $parameter): ?\ReflectionClass
    {
        $type = $parameter->getType();

        /** @psalm-suppress ArgumentTypeCoercion */
        return $type instanceof \ReflectionNamedType && false === $type->isBuiltin()
            ? new \ReflectionClass($type->getName())
            : null;
    }
}
