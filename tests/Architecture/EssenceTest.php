<?php

declare(strict_types=1);

namespace tests\Architecture;

use tests\TestCase;

final class EssenceTest extends TestCase
{
    public function test_layer_essence(): void
    {
        $objectParts = $this->layer()
            ->leaveByNameStart('PHPUnit\\Architecture\\Asserts\\')
            ->leaveByNameRegex('/Elements\\\\Object[^\\\\]+$/');

        /** @var string[] $visibilities */
        $visibilities = $objectParts->essence('properties.*.visibility');

        $this->assertNotOne(
            $visibilities,
            fn(string $visibility) => $visibility === 'private',
            fn(string|int $key, string $visibility) => "Property $key : {$visibility} is not private"
        );
    }
}
