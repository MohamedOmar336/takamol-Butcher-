<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $customers = Customer::when($q, function($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->paginate(15);

        return view('admin.customers.index', compact('customers', 'q'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم إضافة العميل بنجاح.' : 'Customer created successfully.'
        );
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id . '|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم تحديث بيانات العميل بنجاح.' : 'Customer updated successfully.'
        );
    }

    public function show(Customer $customer)
    {
        $orders = $customer->orders()->latest()->paginate(10);
        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function payDebt(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $amount = floatval($validated['amount']);

        if ($amount > floatval($customer->balance)) {
            return back()->withErrors([
                'amount' => app()->getLocale() === 'ar' 
                    ? 'المبلغ المدفوع أكبر من المديونية الحالية للعميل.' 
                    : 'The payment amount exceeds the customer\'s current debt balance.'
            ]);
        }

        $customer->decrement('balance', $amount);

        return redirect()->route('admin.customers.show', $customer->id)->with('success', 
            app()->getLocale() === 'ar' ? 'تم تسجيل دفعة الحساب وتخفيض الدين بنجاح.' : 'Debt payment registered successfully.'
        );
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 
            app()->getLocale() === 'ar' ? 'تم حذف العميل بنجاح.' : 'Customer deleted successfully.'
        );
    }
}
