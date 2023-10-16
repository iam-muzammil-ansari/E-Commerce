<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        $allproducts = Product::latest()->get();
        return view('admin.allproducts', compact('allproducts'));
    }

    public function AddProduct() {
        $categories = Category::latest()->get();
        $subcategories = Subcategory::latest()->get();
        return view('admin.addproduct', compact('categories','subcategories'));
    }

    public function StoreProduct(Request $request) {
        $request->validate([
            'product_name' => 'required|unique:products',
            'price' => 'required',
            'quantity' => 'required',
            'product_short_desc' => 'required',
            'product_long_desc' => 'required',
            'product_category_id' => 'required',
            'product_subcategory_id' => 'required',
            'product_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $request->file('product_img');
        $img_name = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $image->move(public_path('upload'), $img_name);
        $img_url = 'upload/' . $img_name;

        $category_id = $request->product_category_id;
        $subcategory_id = $request->product_subcategory_id;

        $category_name = Category::where('id', $category_id)->value('category_name');
        $subcategory_name = Subcategory::where('id', $subcategory_id)->value('subcategory_name');

        Product::create([
            'product_name' => $request->product_name,
            'product_short_desc' => $request->product_short_desc,
            'product_long_desc' => $request->product_long_desc,
            'price' => $request->price,
            'product_category_name' => $category_name,
            'product_subcategory_name' => $subcategory_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->product_subcategory_id,
            'product_img' => $img_url,
            'quantity' => $request->quantity,
            'slug' => strtolower(str_replace(' ', '-', $request->product_name)),
        ]);

        Category::where('id', $category_id)->increment('product_count',1);
        Subcategory::where('id', $subcategory_id)->increment('product_count',1);

        return redirect()->route('allproducts')->with('message', 'Product Added Successfully!!');
    }

    public function EditProductImg($id) {
        $productinfo = Product::findOrFail($id);
        return view('admin.editproductimg', compact('productinfo'));
    }

    public function UpdateProductImg(Request $request) {
        $request->validate([
            'product_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $id = $request->id;
        $image = $request->file('product_img');
        $img_name = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $image->move(public_path('upload'), $img_name);
        $img_url = 'upload/' . $img_name;

        Product::findORFail($id)->update([
            'product_img' => $img_url,
        ]);

        return redirect()->route('allproducts')->with('message', 'Product Image Updated Successfully!!');
    }

    public function EditProduct($id) {
        $productinfo = Product::findOrFail($id);
        return view('admin.editproduct', compact('productinfo'));
    }

    public function UpdateProduct(Request $request) {
        $productid = $request->id;

        $request->validate([
            'product_name' => 'required|unique:products',
            'price' => 'required',
            'quantity' => 'required',
            'product_short_desc' => 'required',
            'product_long_desc' => 'required',
        ]);

        Product::findOrfail($productid)->update([
            'product_name' => $request->product_name,
            'product_short_desc' => $request->product_short_desc,
            'product_long_desc' => $request->product_long_desc,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'slug' => strtolower(str_replace(' ', '-', $request->product_name)),
        ]);
        return redirect()->route('allproducts')->with('message', 'Product Updated Successfully!!');
    }

    public function DeleteProduct($id) {
        $category_id = Product::where('id',$id)->value('product_category_id');
        $subcategory_id = Product::where('id',$id)->value('product_subcategory_id');
        Product::findOrFail($id)->delete();
        Category::where('id',$category_id)->decrement('product_count',1);
        Subcategory::where('id',$subcategory_id)->decrement('product_count',1);

        return redirect()->route('allproducts')->with('message', 'Product Deleted Successfully!!');
    }
}
