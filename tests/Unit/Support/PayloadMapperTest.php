<?php

namespace Tests\Unit\Support;

use App\Support\PayloadMapper;
use PHPUnit\Framework\TestCase;

class PayloadMapperTest extends TestCase
{
    public function test_map_splits_scalar_columns_and_overflow_payload(): void
    {
        // Arrange
        $data = [
            'id' => 'doc-1',
            'name' => 'Fashion',
            'isActive' => true,
            'adminCommision' => ['commission' => 10, 'type' => 'fixed'],
            'unknownField' => 'extra',
        ];
        $fillable = ['id', 'name', 'isActive', 'adminCommision', 'payload'];

        // Act
        $result = PayloadMapper::map($data, $fillable, ['payload']);

        // Assert
        $this->assertSame('doc-1', $result['attributes']['id']);
        $this->assertSame('Fashion', $result['attributes']['name']);
        $this->assertTrue($result['attributes']['isActive']);
        $this->assertSame(
            ['commission' => 10, 'type' => 'fixed'],
            $result['overflow']['adminCommision']
        );
        $this->assertSame('extra', $result['overflow']['unknownField']);
    }

    public function test_map_converts_empty_strings_to_null(): void
    {
        // Arrange
        $data = ['name' => '', 'color' => '#fff'];

        // Act
        $result = PayloadMapper::map($data, ['name', 'color'], ['payload']);

        // Assert
        $this->assertNull($result['attributes']['name']);
        $this->assertSame('#fff', $result['attributes']['color']);
    }

    public function test_map_decodes_json_string_for_json_columns(): void
    {
        // Arrange
        $data = ['payload' => '{"foo":"bar"}'];

        // Act
        $result = PayloadMapper::map($data, ['payload'], ['payload']);

        // Assert
        $this->assertSame(['foo' => 'bar'], $result['attributes']['payload']);
    }

    public function test_parse_timestamp_from_firestore_seconds(): void
    {
        // Arrange
        $value = ['_seconds' => 1609459200];

        // Act
        $parsed = PayloadMapper::parseTimestamp($value);

        // Assert
        $this->assertSame('2021-01-01 00:00:00', $parsed);
    }

    public function test_parse_timestamp_from_datatype_wrapper(): void
    {
        // Arrange
        $value = [
            '__datatype__' => 'timestamp',
            'value' => ['_seconds' => 1609459200],
        ];

        // Act
        $parsed = PayloadMapper::parseTimestamp($value);

        // Assert
        $this->assertSame('2021-01-01 00:00:00', $parsed);
    }

    public function test_parse_timestamp_returns_null_for_empty_values(): void
    {
        $this->assertNull(PayloadMapper::parseTimestamp(null));
        $this->assertNull(PayloadMapper::parseTimestamp(''));
    }

    public function test_parse_timestamp_from_numeric_and_string_inputs(): void
    {
        $this->assertSame('2021-01-01 00:00:00', PayloadMapper::parseTimestamp(1609459200));
        $this->assertSame('2021-01-01 00:00:00', PayloadMapper::parseTimestamp('2021-01-01 00:00:00'));
    }
}
