<?php

namespace App\Services\Excel;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImporter
{
    /**
     * Import products from Excel/CSV file.
     * Returns a report array.
     */
    public function import(string $filePath): array
    {
        $report = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                $report['errors'][] = app()->getLocale() === 'ar'
                    ? 'الملف فارغ أو لا يحتوي على بيانات.'
                    : 'The file is empty or has no data rows.';
                return $report;
            }

            // Parse headers
            $headers = array_map(function($header) {
                return trim(Str::lower((string)$header));
            }, $rows[0]);

            $headerMapping = $this->mapHeaders($headers);

            // Iterate through rows (skipping header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip completely empty rows
                if (empty(array_filter($row, function($v) { return $v !== null && $v !== ''; }))) {
                    continue;
                }

                $sku = trim((string)$this->getValueByMappedHeader($row, $headerMapping, 'sku'));
                $nameEn = trim((string)$this->getValueByMappedHeader($row, $headerMapping, 'name_en'));
                $nameAr = trim((string)$this->getValueByMappedHeader($row, $headerMapping, 'name_ar'));
                $price = $this->getValueByMappedHeader($row, $headerMapping, 'price');
                $pricingTypeRaw = trim((string)$this->getValueByMappedHeader($row, $headerMapping, 'pricing_type'));
                $stockRaw = $this->getValueByMappedHeader($row, $headerMapping, 'stock');
                $categoryName = trim((string)$this->getValueByMappedHeader($row, $headerMapping, 'category'));

                // Fallbacks for missing names
                if (!$nameAr && $nameEn) {
                    $nameAr = $nameEn;
                }
                if (!$nameEn && $nameAr) {
                    $nameEn = $nameAr;
                }

                if (!$nameAr && !$nameEn) {
                    $report['errors'][] = (app()->getLocale() === 'ar' ? 'السطر ' : 'Row ') . ($i + 1) . ': ' . (app()->getLocale() === 'ar' ? 'اسم المنتج مطلوب.' : 'Product name is required.');
                    $report['skipped']++;
                    continue;
                }

                // If SKU is empty, auto-generate PLU/SKU
                if (!$sku) {
                    $sku = 'SKU-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                } else {
                    // Trim leading zeroes if scale barcode string like 00002 -> 2
                    if (strlen($sku) > 1 && substr($sku, 0, 4) === '0000') {
                        $sku = ltrim($sku, '0');
                        if ($sku === '') {
                            $sku = '0';
                        }
                    }
                }

                // Parse Category
                $category = null;
                if ($categoryName) {
                    $category = Category::where('name_en', $categoryName)
                        ->orWhere('name_ar', $categoryName)
                        ->first();

                    if (!$category) {
                        $category = Category::create([
                            'name_en' => $categoryName,
                            'name_ar' => $categoryName,
                            'slug' => Str::slug($categoryName) ?: 'cat-' . Str::random(5)
                        ]);
                    }
                } else {
                    $category = Category::firstOrCreate(
                        ['slug' => 'uncategorized'],
                        ['name_en' => 'Uncategorized', 'name_ar' => 'غير مصنف']
                    );
                }

                // Normalize Pricing Type (0 = weight, 1 = piece)
                $pricingTypeRawLower = Str::lower($pricingTypeRaw);
                if ($pricingTypeRawLower === '0' || in_array($pricingTypeRawLower, ['weight', 'weighed', 'kg', 'كيلو', 'وزن'])) {
                    $pricingType = 'weight';
                } else {
                    $pricingType = 'piece';
                }

                // Normalize numbers
                $price = floatval($price);
                $stock = ($stockRaw !== null && $stockRaw !== '') ? floatval($stockRaw) : 100.00;

                // Check if SKU exists
                $product = Product::where('sku', $sku)->first();

                if ($product) {
                    $product->update([
                        'category_id' => $category->id,
                        'name_en' => $nameEn,
                        'name_ar' => $nameAr,
                        'price' => $price,
                        'pricing_type' => $pricingType,
                        'stock' => $stock,
                    ]);
                    $report['updated']++;
                } else {
                    Product::create([
                        'category_id' => $category->id,
                        'sku' => $sku,
                        'name_en' => $nameEn,
                        'name_ar' => $nameAr,
                        'price' => $price,
                        'pricing_type' => $pricingType,
                        'stock' => $stock,
                        'is_active' => true
                    ]);
                    $report['created']++;
                }
            }
        } catch (\Exception $e) {
            $report['errors'][] = (app()->getLocale() === 'ar' ? 'فشل استيراد الملف: ' : 'Failed to import file: ') . $e->getMessage();
        }

        return $report;
    }

    private function mapHeaders(array $headers): array
    {
        $mapping = [];

        foreach ($headers as $index => $header) {
            $h = Str::lower(trim($header));

            if (Str::contains($h, ['sku', 'plu', 'code', 'barcode', 'الباركود', 'باركود', 'كود'])) {
                $mapping['sku'] = $index;
            } elseif (Str::contains($h, ['name_en', 'name (en)', 'english name', 'الاسم بالانجليزية', 'الاسم بالإنجليزية', 'الاسم انجليزي'])) {
                $mapping['name_en'] = $index;
            } elseif (Str::contains($h, ['name_ar', 'name (ar)', 'arabic name', 'الاسم بالعربية', 'الاسم عربي', 'اسم'])) {
                $mapping['name_ar'] = $index;
            } elseif (Str::contains($h, ['price', 'unit price', 'rate', 'sale price', 'السعر', 'سعر'])) {
                $mapping['price'] = $index;
            } elseif (Str::contains($h, ['pricing_type', 'pricing type', 'type', 'pricing', 'method', 'نوع التسعير', 'نوع السعر'])) {
                $mapping['pricing_type'] = $index;
            } elseif (Str::contains($h, ['stock', 'quantity', 'qty', 'المخزون', 'الكمية', 'كمية'])) {
                $mapping['stock'] = $index;
            } elseif (Str::contains($h, ['category', 'category name', 'القسم', 'قسم', 'الفئة'])) {
                $mapping['category'] = $index;
            }
        }

        return $mapping;
    }

    private function getValueByMappedHeader(array $row, array $mapping, string $key)
    {
        if (isset($mapping[$key]) && isset($row[$mapping[$key]])) {
            return $row[$mapping[$key]];
        }
        return null;
    }
}
