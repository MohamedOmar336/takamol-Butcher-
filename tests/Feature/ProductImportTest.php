<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\Excel\ProductImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_import_products_from_csv()
    {
        // 1. Create a dummy CSV file content
        $csvContent = "SKU,الاسم بالعربية,الاسم بالإنجليزية,Price,Pricing Type,Stock,Category\n" .
                      "02222,كبدة بقري طازجة,Fresh Beef Liver,380.00,weight,12.500,لحوم طازجة\n" .
                      "02223,ممبار بلدي,Baladi Mombar,180.00,piece,25.000,مستلزمات جزارة\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_import');
        file_put_contents($tempFile, $csvContent);

        // 2. Call the importer
        $importer = new ProductImporter();
        $report = $importer->import($tempFile);

        // 3. Assert report results
        $this->assertEquals(2, $report['created']);
        $this->assertEquals(0, $report['updated']);
        $this->assertEquals(0, $report['skipped']);
        $this->assertEmpty($report['errors']);

        // 4. Verify Database records
        $this->assertDatabaseHas('products', [
            'sku' => '02222',
            'name_en' => 'Fresh Beef Liver',
            'name_ar' => 'كبدة بقري طازجة',
            'price' => 380.00,
            'pricing_type' => 'weight',
            'stock' => 12.500
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => '02223',
            'name_en' => 'Baladi Mombar',
            'name_ar' => 'ممبار بلدي',
            'price' => 180.00,
            'pricing_type' => 'piece',
            'stock' => 25.000
        ]);

        // Clean up temp file
        unlink($tempFile);
    }
}
