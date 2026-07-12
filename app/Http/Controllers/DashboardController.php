<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today('Africa/Cairo');

        // Core stats
        $totalSalesToday = Order::where('status', 'completed')->whereDate('created_at', $today)->sum('total_amount');
        $totalOrdersToday = Order::where('status', 'completed')->whereDate('created_at', $today)->count();
        
        // Weighed items stock below 5.000 kg OR pieces below 5 units
        $lowStockCount = Product::where('stock', '<', 5.000)->count();

        // Count of customers with active debt
        $indebtedCustomersCount = Customer::where('balance', '>', 0.00)->count();

        // Recent transactions
        $recentOrders = Order::with(['customer', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Weekly sales graph data (last 7 days) - Grouped in PHP for timezone correctness
        $ordersLast7Days = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now('Africa/Cairo')->subDays(6)->startOfDay())
            ->get();

        // Fill missing days with zero
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now('Africa/Cairo')->subDays($i)->format('Y-m-d');
            $chartData[$dateStr] = 0.00;
        }

        // Populate sales amounts matching the date in Egypt timezone
        foreach ($ordersLast7Days as $order) {
            $orderDate = $order->created_at->setTimezone('Africa/Cairo')->format('Y-m-d');
            if (isset($chartData[$orderDate])) {
                $chartData[$orderDate] += (float)$order->total_amount;
            }
        }

        return view('admin.dashboard', compact(
            'totalSalesToday', 
            'totalOrdersToday', 
            'lowStockCount', 
            'indebtedCustomersCount', 
            'recentOrders',
            'chartData'
        ));
    }

    public function usersIndex()
    {
        $users = User::with('permissions')->orderBy('name')->get();
        $permissions = Permission::all();
        return view('admin.users.index', compact('users', 'permissions'));
    }

    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $isAdmin = $request->has('is_admin');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_admin' => $isAdmin
        ]);

        // Sync permissions if not super admin
        if (!$isAdmin && isset($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.users')->with('success', 
            app()->getLocale() === 'ar' ? 'تم إنشاء المستخدم بنجاح.' : 'User created successfully.'
        );
    }

    public function usersUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:6',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $isAdmin = $request->has('is_admin');

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $isAdmin
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        // Sync permissions if not super admin
        if (!$isAdmin) {
            $user->permissions()->sync($request->get('permissions', []));
        } else {
            // Remove permissions from Super Admin (they have everything inherently)
            $user->permissions()->detach();
        }

        return redirect()->route('admin.users')->with('success', 
            app()->getLocale() === 'ar' ? 'تم تحديث بيانات المستخدم بنجاح.' : 'User updated successfully.'
        );
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 
                app()->getLocale() === 'ar' ? 'لا يمكنك حذف حسابك الحالي.' : 'You cannot delete your own account.'
            );
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 
            app()->getLocale() === 'ar' ? 'تم حذف المستخدم بنجاح.' : 'User deleted successfully.'
        );
    }

    public function sendDailyReportManual()
    {
        try {
            // Call artisan command
            Artisan::call('app:send-daily-sales-report');
            
            return redirect()->route('admin.dashboard')->with('success', 
                app()->getLocale() === 'ar' 
                    ? 'تم إرسال التقرير اليومي للمبيعات إلى بريد المالك بنجاح.' 
                    : 'Daily sales report sent to the owner\'s email successfully.'
            );
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 
                (app()->getLocale() === 'ar' ? 'حدث خطأ أثناء إرسال التقرير: ' : 'Failed to send report: ') . $e->getMessage()
            );
        }
    }

    public function ordersIndex(Request $request)
    {
        $query = Order::with(['customer', 'user', 'items.product']);

        // Filter by Date
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        // Filter by Payment Method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Search by Invoice Number or Customer Name/Phone or Cashier
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('cashier_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate recent first
        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function refundOrder(Order $order)
    {
        // 1. Authorization: Only admins can perform refunds
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Validate Order Status
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 
                app()->getLocale() === 'ar' ? 'هذه الفاتورة مرتجعة بالفعل.' : 'This invoice is already refunded.'
            );
        }

        try {
            DB::transaction(function () use ($order) {
                // 3. Update Order Status
                $order->update(['status' => 'refunded']);

                // 4. Restore Inventory Stocks
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                // 5. Adjust Customer Balance if payment was Credit (Debt)
                if ($order->payment_method === 'credit' && $order->customer) {
                    $order->customer->decrement('balance', $order->total_amount);
                }
            });

            return redirect()->back()->with('success', 
                app()->getLocale() === 'ar' ? 'تم استرجاع وإلغاء الفاتورة بنجاح وإعادة المنتجات للمخزن.' : 'Invoice refunded successfully and stocks restored.'
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                (app()->getLocale() === 'ar' ? 'حدث خطأ أثناء معالجة الاسترجاع: ' : 'Failed to process refund: ') . $e->getMessage()
            );
        }
    }

    public function printDailyReport()
    {
        $today = Carbon::today('Africa/Cairo');
        $dateStr = $today->format('Y-m-d');

        // Query today's orders - only completed
        $orders = Order::where('status', 'completed')->whereDate('created_at', $today)->get();
        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalDiscounts = $orders->sum('discount_amount');

        // Payment method breakdown
        $cashSales = $orders->where('payment_method', 'cash')->sum('total_amount');
        $cardSales = $orders->where('payment_method', 'card')->sum('total_amount');
        $creditSales = $orders->where('payment_method', 'credit')->sum('total_amount');

        // Top 5 products sold today
        $topProducts = \App\Models\OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'))
            ->whereHas('order', function($query) use ($today) {
                $query->where('status', 'completed')->whereDate('created_at', $today);
            })
            ->groupBy('product_id')
            ->orderBy('total_subtotal', 'desc')
            ->limit(5)
            ->with('product')
            ->get();

        // Low stock warnings
        $lowStockProducts = Product::where('stock', '<', 5.000)
            ->orderBy('stock')
            ->limit(15)
            ->get();

        return view('admin.reports.print_daily', compact(
            'dateStr',
            'totalSales',
            'totalOrders',
            'totalDiscounts',
            'cashSales',
            'cardSales',
            'creditSales',
            'topProducts',
            'lowStockProducts'
        ));
    }
}
