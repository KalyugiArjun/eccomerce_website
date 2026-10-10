<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryBrandController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->whereNull('parent_id')->orderBy('sort_order')->get();
        $brands = Brand::withCount('products')->get();

        return view('admin.categories.index', compact('categories', 'brands'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:50',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'icon' => $request->icon ?: 'bi-tag',
            'commission_rate' => $request->commission_rate ?: 10.0,
        ]);

        return back()->with('success', "Category '{$request->name}' created successfully!");
    }

    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', "Brand '{$request->name}' created successfully!");
    }
}
