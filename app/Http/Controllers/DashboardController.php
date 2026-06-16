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
        $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_amount');
        $totalOrdersToday = Order::whereDate('created_at', $today)->count();
        
        // Weighed items stock below 5.000 kg OR pieces below 5 units
        $lowStockCount = Product::where('stock', '<', 5.000)->count();

        // Count of customers with active debt
        $indebtedCustomersCount = Customer::where('balance', '>', 0.00)->count();

        // Recent transactions
        $recentOrders = Order::with(['customer', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Weekly sales graph data (last 7 days)
        $weeklySales = Order::select(
                DB::raw("DATE(created_at) as sale_date"),
                DB::raw("SUM(total_amount) as total_amount")
            )
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->pluck('total_amount', 'sale_date')
            ->toArray();

        // Fill missing days with zero
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now('Africa/Cairo')->subDays($i)->format('Y-m-d');
            $chartData[$dateStr] = $weeklySales[$dateStr] ?? 0.00;
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
}
