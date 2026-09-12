<?php

namespace Tests\Unit;

use App\Services\Geo;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_distance_handles_zero_antimeridian_and_antipodes(): void
    {
        $this->assertSame(0.0, Geo::meters(0, 0, 0, 0));
        $this->assertEqualsWithDelta(22239, Geo::meters(0, 179.9, 0, -179.9), 2);
        $this->assertEqualsWithDelta(20015087, Geo::meters(0, 0, 0, 180), 2);
    }
}
