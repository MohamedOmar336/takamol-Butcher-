<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\Excel\ProductImporter;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $categoryId = $request->get('category_id');

        $categories = Category::orderBy('name_ar')->get();
        
        $products = Product::with('category')
            ->when($q, function($query, $search) {
                $query->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
            })
            ->when($categoryId, function($query, $catId) {
                $query->where('category_id', $catId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.products.index', compact('products', 'categories', 'q', 'categoryId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku|max:50',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:weight,piece',
            'stock' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم إضافة المنتج بنجاح.' : 'Product created successfully.'
        );
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:50',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:weight,piece',
            'stock' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم تحديث المنتج بنجاح.' : 'Product updated successfully.'
        );
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم حذف المنتج بنجاح.' : 'Product deleted successfully.'
        );
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:4096'
        ]);

        $file = $request->file('import_file');
        
        $importer = new ProductImporter();
        $report = $importer->import($file->getRealPath());

        $msg = app()->getLocale() === 'ar'
            ? "تم الاستيراد: تم إضافة {$report['created']} منتج جديد، وتحديث {$report['updated']} منتج. تخطي: {$report['skipped']}."
            : "Import complete: {$report['created']} created, {$report['updated']} updated, {$report['skipped']} skipped.";

        if (!empty($report['errors'])) {
            return redirect()->route('admin.products.index')
                ->with('success', $msg)
                ->with('import_errors', $report['errors']);
        }

        return redirect()->route('admin.products.index')->with('success', $msg);
    }
}
