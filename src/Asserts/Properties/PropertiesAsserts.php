<?php

declare(strict_types=1);

namespace PHPUnit\Architecture\Asserts\Properties;

use PHPUnit\Architecture\Elements\Layer\Layer;

/**
 * Asserts for objects methods
 */
trait PropertiesAsserts
{
    /**
     * Search public properties in layerA
     *
     * @param Layer|Layer[] $layerA
     */
    public function assertHasNotPublicProperties($layerA): void
    {
        /** @var Layer[] $layers */
        $layers = is_array($layerA) ? $layerA : [$layerA];

        $result = [];
        foreach ($layers as $layer) {
            foreach ($layer as $object) {
                foreach ($object->properties as $property) {
                    if ($property->visibility === 'public') {
                        $result[] = "$object->name : {$property->name} <- public";
                    }
                }
            }
        }

        self::assertEquals(
            0,
            count($result),
            'Found public property: ' . implode("\n", $result)
        );
    }

    abstract public static function assertEquals($expected, $actual, string $message = ''): void;
}
