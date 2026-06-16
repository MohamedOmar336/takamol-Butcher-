<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Mail\DailySalesReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-sales-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compiles today\'s sales statistics and sends an HTML report to the owner.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Compiling daily sales report...');

        $today = Carbon::today('Africa/Cairo');
        $dateStr = $today->format('Y-m-d');

        // Query today's orders
        $orders = Order::whereDate('created_at', $today)->get();
        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalDiscounts = $orders->sum('discount_amount');

        // Payment method breakdown
        $cashSales = $orders->where('payment_method', 'cash')->sum('total_amount');
        $cardSales = $orders->where('payment_method', 'card')->sum('total_amount');
        $creditSales = $orders->where('payment_method', 'credit')->sum('total_amount');

        // Top 5 products sold today
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'))
            ->whereHas('order', function($query) use ($today) {
                $query->whereDate('created_at', $today);
            })
            ->groupBy('product_id')
            ->orderBy('total_subtotal', 'desc')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function($item) {
                return [
                    'name_ar' => $item->product ? $item->product->name_ar : 'منتج محذوف',
                    'name_en' => $item->product ? $item->product->name_en : 'Deleted Product',
                    'sku' => $item->product ? $item->product->sku : '-',
                    'qty' => $item->total_qty,
                    'type' => $item->product ? $item->product->pricing_type : 'piece',
                    'total' => $item->total_subtotal
                ];
            })
            ->toArray();

        // Low stock warnings (stock < 5.000)
        $lowStockProducts = Product::where('stock', '<', 5.000)
            ->orderBy('stock')
            ->limit(10)
            ->get()
            ->map(function($p) {
                return [
                    'name_ar' => $p->name_ar,
                    'name_en' => $p->name_en,
                    'sku' => $p->sku,
                    'stock' => $p->stock,
                    'type' => $p->pricing_type
                ];
            })
            ->toArray();

        $stats = [
            'date' => $dateStr,
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_discounts' => $totalDiscounts,
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'credit_sales' => $creditSales,
            'top_products' => $topProducts,
            'low_stock' => $lowStockProducts
        ];

        // Send Email
        $ownerEmail = env('OWNER_EMAIL', 'owner@example.com');
        
        $this->info("Sending report email to: {$ownerEmail}");
        
        Mail::to($ownerEmail)->send(new DailySalesReportMail($stats));

        $this->info('Report sent successfully!');
        return self::SUCCESS;
    }
}
