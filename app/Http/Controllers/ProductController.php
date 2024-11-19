<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')){
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('manufacturer')){
            $query->where('manufacturer_name', $request->manufacturer);
        }

        $products = $query->get();

        $companies = Company::all();
        return view('products.index', compact('products', 'companies'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('products.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:225',
            'manufacturer_name' => 'required|string|exists:companies,name',
            'price' => 'required|string|max:225',
            'stock_quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,pnj,jpg',
            'detail' => 'nullable|string',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->manufacturer_name = $request->manufacturer_name;
        $product->price = $request->price;
        $product->stock_quantity = $request->stock_quantity;
        $product->detail_display = $request->detail_display;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image_path = $imagePath; 
        }

        else{
            $product->image = null;
        }

        $product->save();
        return redirect()->route('products.index')->with('success', '商品が登録されました。');

    }

    public function show(Product $product)
    {
        $products = Product::all();
        $index = $products->search(function ($item) use($product){
            return $item->id === $product->id;
        });

        return view('products.show', compact('product', 'index'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品が削除されました。');

    }

    public function edit(Product $product)
    {
        $companies = Company::all();
        return view('products.edit', compact('product', 'companies'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:225',
            'manufacturer_name' => 'required|string|max:100', 
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'detail_display' => 'nullable|string',
        ]);

        $product->name = $request->name;
        $product->manufacturer_name = $request->manufacturer_name;
        $product->price = $request->price;
        $product->stock_quantity = $request->stock_quantity;
        $product->detail_display = $request->detail_display;

        if($request->hasFile('image')) {
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->save();
        return redirect()->route('products.index')->with('success', '商品が更新されました。');

    }
}
