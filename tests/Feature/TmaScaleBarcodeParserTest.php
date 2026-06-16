<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Scale\TmaScaleBarcodeParser;

class TmaScaleBarcodeParserTest extends TestCase
{
    public function test_it_can_parse_valid_tma_scale_barcode()
    {
        $parser = new TmaScaleBarcodeParser();
        $payload = $parser->parse('2011135015034');

        $this->assertTrue($payload->isValid);
        $this->assertEquals('01113', $payload->sku);
        $this->assertEquals(1.503, $payload->weight);
        $this->assertNull($payload->error);
    }

    public function test_it_fails_on_invalid_length_barcode()
    {
        $parser = new TmaScaleBarcodeParser();
        $payload = $parser->parse('20111350150');

        $this->assertFalse($payload->isValid);
        $this->assertNotEmpty($payload->error);
    }

    public function test_it_fails_on_non_scale_prefix()
    {
        $parser = new TmaScaleBarcodeParser();
        $payload = $parser->parse('1011135015034'); // starts with 1 instead of 2

        $this->assertFalse($payload->isValid);
        $this->assertNotEmpty($payload->error);
    }
}
