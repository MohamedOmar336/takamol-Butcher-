<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the drivers.
     */
    public function index()
    {
        $drivers = Driver::orderBy('name', 'asc')->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Driver::create($validated);

        return redirect()->route('admin.drivers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم إضافة الطيار بنجاح.' : 'Driver added successfully.'
        );
    }

    /**
     * Update the specified driver.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $driver->update($validated);

        return redirect()->route('admin.drivers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم تحديث بيانات الطيار بنجاح.' : 'Driver updated successfully.'
        );
    }

    /**
     * Remove the specified driver.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم حذف الطيار بنجاح.' : 'Driver deleted successfully.'
        );
    }
}
