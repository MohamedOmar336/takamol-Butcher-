<?php

namespace App\Services\Scale;

class TmaScaleBarcodeParser implements ScaleParserInterface
{
    /**
     * Parses a TM-A Scale EAN-13 barcode.
     * Expected format:
     * - Digit 1: '2' (internal variable-weight prefix)
     * - Digits 2-6: PLU / SKU (5 digits, e.g. '01113')
     * - Digit 7: Weight checksum / scale parameter (1 digit, e.g. '5')
     * - Digits 8-12: Weight in grams (5 digits, e.g. '01503' = 1.503 kg)
     * - Digit 13: EAN-13 checksum (1 digit, e.g. '4')
     */
    public function parse(string $barcode): ScalePayload
    {
        // Remove whitespace
        $barcode = trim($barcode);

        // Standard retail scale barcode is EAN-13 (13 digits)
        if (strlen($barcode) !== 13) {
            return new ScalePayload(
                sku: '',
                weight: 0.00,
                isValid: false,
                error: app()->getLocale() === 'ar' 
                    ? 'الباركود غير صالح، يجب أن يكون بطول 13 رقماً.' 
                    : 'Invalid barcode. Must be exactly 13 digits.'
            );
        }

        // Must start with '2' (standard variable weight prefix)
        if ($barcode[0] !== '2') {
            return new ScalePayload(
                sku: '',
                weight: 0.00,
                isValid: false,
                error: app()->getLocale() === 'ar'
                    ? 'باركود غير مدعوم، باركود الميزان يجب أن يبدأ بالرقم 2.'
                    : 'Unsupported barcode. Scale barcode must start with digit 2.'
            );
        }

        // Extract SKU/PLU (digits index 1 to 5, length 5)
        $sku = substr($barcode, 1, 5);

        // Extract Weight in grams (digits index 7 to 11, length 5)
        $weightStr = substr($barcode, 7, 5);

        if (!ctype_digit($sku) || !ctype_digit($weightStr)) {
            return new ScalePayload(
                sku: '',
                weight: 0.00,
                isValid: false,
                error: app()->getLocale() === 'ar'
                    ? 'الباركود يحتوي على رموز غير صالحة.'
                    : 'Barcode contains invalid non-numeric characters.'
            );
        }

        // Convert weight in grams to kilograms (e.g. 1503g = 1.503 kg)
        $weightKg = (float)$weightStr / 1000;

        return new ScalePayload(
            sku: $sku,
            weight: $weightKg,
            isValid: true
        );
    }
}
