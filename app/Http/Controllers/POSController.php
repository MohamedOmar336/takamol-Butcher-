<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Scale\TmaScaleBarcodeParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', true);
        }])->get();

        // All active products for direct lookup/search
        $products = Product::where('is_active', true)->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function searchCustomer(Request $request)
    {
        $q = $request->get('q');
        if (!$q) return response()->json([]);

        $customers = Customer::where('phone', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function quickAddCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 1000.00, // Default 1000 EGP credit
            'balance' => 0.00
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => app()->getLocale() === 'ar' ? 'تم إضافة العميل بنجاح.' : 'Customer added successfully.'
        ]);
    }

    public function scanBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        if (!$barcode) {
            return response()->json(['success' => false, 'message' => 'No barcode provided.']);
        }

        $parser = new TmaScaleBarcodeParser();
        $payload = $parser->parse($barcode);

        if (!$payload->isValid) {
            return response()->json([
                'success' => false,
                'message' => $payload->error
            ]);
        }

        // Search product by PLU/SKU
        $product = Product::where('sku', $payload->sku)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? "المنتج ذو الكود {$payload->sku} غير موجود."
                    : "Product with SKU {$payload->sku} not found."
            ]);
        }

        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? "المنتج '{$product->name}' غير نشط حالياً."
                    : "Product '{$product->name}' is inactive."
            ]);
        }

        // Calculate total price based on database price per kg
        $calculatedPrice = round($payload->weight * (float)$product->price, 2);

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float)$product->price,
                'pricing_type' => $product->pricing_type,
            ],
            'scanned_weight' => $payload->weight,
            'scanned_price' => $calculatedPrice
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,credit',
            'discount_amount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $cart = $validated['cart'];
        $paymentMethod = $validated['payment_method'];
        $discountAmount = floatval($validated['discount_amount'] ?? 0.00);
        $customerId = $validated['customer_id'] ?? null;
        $cashier = auth()->user();

        // If paying on credit, a customer MUST be linked
        if ($paymentMethod === 'credit' && !$customerId) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'يجب ربط العميل بالطلب عند اختيار الدفع الآجل (بالأجل).'
                    : 'A customer must be linked to buy on credit.'
            ], 422);
        }

        try {
            $orderId = DB::transaction(function () use ($cart, $paymentMethod, $discountAmount, $customerId, $cashier) {
                $totalAmount = 0.00;
                $itemsToCreate = [];
                $stockUpdates = [];

                // 1. Process items and validate stock
                foreach ($cart as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $qty = floatval($item['quantity']);

                    // Verify stock
                    if ($product->stock < $qty) {
                        throw new \Exception(
                            app()->getLocale() === 'ar'
                                ? "الكمية المطلوبة للمنتج ({$product->name}) غير متوفرة. المتاح حالياً: {$product->stock}"
                                : "Insufficient stock for product ({$product->name}). Current stock: {$product->stock}"
                        );
                    }

                    $subtotal = round($qty * (float)$product->price, 2);
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => (float)$product->price,
                        'subtotal' => $subtotal
                    ];

                    $stockUpdates[] = [
                        'product' => $product,
                        'qty' => $qty
                    ];
                }

                $netTotal = max(0.00, $totalAmount - $discountAmount);

                // 2. If credit, check limit
                if ($paymentMethod === 'credit' && $customerId) {
                    $customer = Customer::lockForUpdate()->find($customerId);
                    $newBalance = floatval($customer->balance) + $netTotal;
                    if ($newBalance > floatval($customer->credit_limit)) {
                        throw new \Exception(
                            app()->getLocale() === 'ar'
                                ? "لقد تجاوز العميل الحد الائتماني المسموح به. الحد الحالي: {$customer->credit_limit} ج.م.، الدين بعد هذه المعاملة: {$newBalance} ج.م."
                                : "Customer credit limit exceeded. Current limit: {$customer->credit_limit} EGP. Total debt after this order: {$newBalance} EGP."
                        );
                    }
                    $customer->update(['balance' => $newBalance]);
                }

                // 3. Create Order record
                $orderNumber = 'BTCH-' . date('YmdHis') . '-' . rand(100, 999);
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_id' => $customerId,
                    'user_id' => $cashier->id,
                    'payment_method' => $paymentMethod,
                    'total_amount' => $netTotal,
                    'discount_amount' => $discountAmount,
                    'cashier_name' => $cashier->name
                ]);

                // 4. Save items & Decrement stock
                foreach ($itemsToCreate as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                foreach ($stockUpdates as $update) {
                    $prod = $update['product'];
                    $prod->decrement('stock', $update['qty']);
                }

                return $order->id;
            });

            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'message' => app()->getLocale() === 'ar' ? 'تم حفظ الفاتورة بنجاح.' : 'Order checked out successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function printReceipt(Order $order)
    {
        $order->load(['items.product', 'customer', 'user']);
        return view('pos.receipt', compact('order'));
    }
}
