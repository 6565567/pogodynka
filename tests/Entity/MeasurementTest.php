<?php

namespace App\Tests\Entity;

use App\Entity\Measurement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    public static function dataGetFahrenheit(): array
    {
        return [
            ['0', '32'],
            ['-100', '-148'],
            ['100', '212'],
            ['0.5', '32.9'],
            ['-0.5', '31.1'],
            ['1.5', '34.7'],
            ['-1.5', '29.3'],
            ['20.5', '68.9'],
            ['-20.5', '-4.9'],
            ['37.5', '99.5'],
        ];
    }

    #[DataProvider('dataGetFahrenheit')]
    public function testGetFahrenheit(string $celsius, string $expectedFahrenheit): void
    {
        $measurement = new Measurement();
        $measurement->setTemperature($celsius);

        $this->assertEqualsWithDelta(
            (float) $expectedFahrenheit,
            $measurement->getFahrenheit(),
            0.000001
        );
    }
}
