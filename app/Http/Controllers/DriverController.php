<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::withCount(['orders' => function($q) {
            $q->whereDate('created_at', Carbon::today());
        }])->get();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
        ]);

        Driver::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'vehicle_type' => $request->vehicle_type ?? 'دراجة نارية',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم إضافة الطيار بنجاح.' : 'Driver added successfully.');
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $driver->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'vehicle_type' => $request->vehicle_type,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث بيانات الطيار بنجاح.' : 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطيار بنجاح.' : 'Driver deleted successfully.');
    }

    /**
     * End-of-Day Driver Cash & Delivery Settlement Report
     */
    public function settlementReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDriverId = $request->get('driver_id');

        $drivers = Driver::orderBy('name')->get();

        $query = Order::with(['driver', 'customer'])
            ->whereNotNull('driver_id')
            ->whereDate('created_at', $date);

        if ($selectedDriverId) {
            $query->where('driver_id', $selectedDriverId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // Group summary per driver
        $driverSummaries = Order::select(
                'driver_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_collected'),
                DB::raw('SUM(delivery_fee) as total_delivery_fees')
            )
            ->whereNotNull('driver_id')
            ->whereDate('created_at', $date)
            ->groupBy('driver_id')
            ->with('driver')
            ->get();

        return view('admin.drivers.settlement', compact('date', 'selectedDriverId', 'drivers', 'orders', 'driverSummaries'));
    }
}
