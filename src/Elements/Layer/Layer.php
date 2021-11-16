<?php

declare(strict_types=1);

namespace PHPUnit\Architecture\Elements\Layer;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use PHPUnit\Architecture\Elements\ObjectDescription;

final class Layer implements IteratorAggregate
{
    use LayerFilters;
    use LayerSplit;

    protected ?string $name = null;
    /**
     * @var ObjectDescription[]
     */
    protected array $objects = [];

    public function __construct(
        array $objects
    ) {
        $this->objects = $objects;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->objects);
    }

    public function getName(): string
    {
        if ($this->name === null) {
            $objectsName = array_map(static function (ObjectDescription $objectDescription): string {
                return $objectDescription->name;
            }, $this->objects);

            sort($objectsName);

            $this->name = implode(',', $objectsName);
        }

        return $this->name;
    }

    /**
     * Compare layers
     */
    public function equals(Layer $layer): bool
    {
        return $this->getName() === $layer->getName();
    }

    /**
     * @param Closure $closure static function(ObjectDescription $objectDescription): bool
     */
    public function filter(Closure $closure): self
    {
        return new Layer(array_filter($this->objects, $closure));
    }

    /**
     * @param Closure $closure static function(ObjectDescription $objectDescription): ?string
     * @return static[]
     */
    public function split(Closure $closure): array
    {
        $objects = [];

        foreach ($this->objects as $object) {
            /** @var null|string $key */
            $key = $closure($object);

            if ($key === null) {
                continue;
            }

            if (!isset($objects[$key])) {
                $objects[$key] = [];
            }

            $objects[$key][] = $object;
        }

        return array_map(static function (array $objects): Layer {
            return new Layer($objects);
        }, $objects);
    }
}
