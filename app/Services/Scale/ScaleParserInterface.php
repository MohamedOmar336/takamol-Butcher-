<?php

namespace App\Services\Scale;

interface ScaleParserInterface
{
    /**
     * Parse scale barcode/QR and return a ScalePayload DTO.
     */
    public function parse(string $barcode): ScalePayload;
}
