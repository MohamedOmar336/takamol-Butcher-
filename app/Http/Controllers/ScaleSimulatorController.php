<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ScaleSimulatorController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->get();
        return view('scale.simulator', compact('products'));
    }
}
