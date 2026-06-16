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
                return trim(Str::lower($header));
            }, $rows[0]);

            // Map headers to fields
            // Standard columns we support:
            // - name_en / name (en) / english name / الاسم بالانجليزية
            // - name_ar / name (ar) / arabic name / الاسم بالعربية
            // - sku / plu / code / barcode / الباركود
            // - price / unit price / rate / السعر
            // - pricing_type / type / pricing / نوع التسعير
            // - stock / quantity / qty / المخزون
            // - category / category name / القسم
            
            $headerMapping = $this->mapHeaders($headers);

            // Iterate through rows (skipping header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $sku = $this->getValueByMappedHeader($row, $headerMapping, 'sku');
                $nameEn = $this->getValueByMappedHeader($row, $headerMapping, 'name_en');
                $nameAr = $this->getValueByMappedHeader($row, $headerMapping, 'name_ar');
                $price = $this->getValueByMappedHeader($row, $headerMapping, 'price');
                $pricingType = $this->getValueByMappedHeader($row, $headerMapping, 'pricing_type');
                $stock = $this->getValueByMappedHeader($row, $headerMapping, 'stock');
                $categoryName = $this->getValueByMappedHeader($row, $headerMapping, 'category');

                // Validations
                if (!$sku) {
                    $report['errors'][] = (app()->getLocale() === 'ar' ? 'السطر ' : 'Row ') . ($i + 1) . ': ' . (app()->getLocale() === 'ar' ? 'الباركود/SKU مطلوب.' : 'SKU is required.');
                    $report['skipped']++;
                    continue;
                }

                if (!$nameEn || !$nameAr) {
                    $report['errors'][] = (app()->getLocale() === 'ar' ? 'السطر ' : 'Row ') . ($i + 1) . ': ' . (app()->getLocale() === 'ar' ? 'الاسم باللغتين العربية والإنجليزية مطلوب.' : 'Both English and Arabic names are required.');
                    $report['skipped']++;
                    continue;
                }

                // Parse Category
                $category = null;
                if ($categoryName) {
                    $categoryName = trim($categoryName);
                    // Match by English or Arabic name, or create
                    $category = Category::where('name_en', $categoryName)
                        ->orWhere('name_ar', $categoryName)
                        ->first();

                    if (!$category) {
                        // Create a new category
                        $category = Category::create([
                            'name_en' => $categoryName,
                            'name_ar' => $categoryName, // duplicate if only one is provided
                            'slug' => Str::slug($categoryName) ?: 'cat-' . Str::random(5)
                        ]);
                    }
                } else {
                    // Fallback to default/uncategorized category
                    $category = Category::firstOrCreate(
                        ['slug' => 'uncategorized'],
                        ['name_en' => 'Uncategorized', 'name_ar' => 'غير مصنف']
                    );
                }

                // Normalize Pricing Type
                $pricingType = trim(Str::lower($pricingType));
                if (in_array($pricingType, ['weight', 'weighed', 'kg', 'كيلو', 'وزن'])) {
                    $pricingType = 'weight';
                } else {
                    $pricingType = 'piece'; // Default is by piece
                }

                // Normalize numbers
                $price = floatval($price);
                $stock = floatval($stock);

                // Check if SKU exists
                $product = Product::where('sku', $sku)->first();

                if ($product) {
                    // Update
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
                    // Create
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
            if (in_array($header, ['sku', 'plu', 'code', 'barcode', 'الباركود', 'باركود', 'كود'])) {
                $mapping['sku'] = $index;
            } elseif (in_array($header, ['name_en', 'name (en)', 'english name', 'الاسم بالانجليزية', 'الاسم بالإنجليزية', 'الاسم انجليزي'])) {
                $mapping['name_en'] = $index;
            } elseif (in_array($header, ['name_ar', 'name (ar)', 'arabic name', 'الاسم بالعربية', 'الاسم عربي'])) {
                $mapping['name_ar'] = $index;
            } elseif (in_array($header, ['price', 'unit price', 'rate', 'السعر', 'سعر'])) {
                $mapping['price'] = $index;
            } elseif (in_array($header, ['pricing_type', 'pricing type', 'type', 'pricing', 'نوع التسعير', 'نوع السعر'])) {
                $mapping['pricing_type'] = $index;
            } elseif (in_array($header, ['stock', 'quantity', 'qty', 'المخزون', 'الكمية', 'كمية'])) {
                $mapping['stock'] = $index;
            } elseif (in_array($header, ['category', 'category name', 'القسم', 'قسم', 'الفئة'])) {
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
