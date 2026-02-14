<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category_info')->get()->map(function ($product) {
            $data = $product->toArray();
            $data['category'] = $product->category_info ? $product->category_info->name : $product->category;
            return $data;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Products fetched successfully',
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        // Legacy frontend sends { action: "add", product: {...} } or just JSON?
        // products.php expects $input['product'] inside.
        // We should adapt to the expected input or standard input.
        // If we want to keep frontend compatibility, we might need to look at $request->input('product')

        $data = $request->input('product');
        if (!$data) {
             // Maybe direct input? check
             $data = $request->all();
             if(isset($data['product'])) $data = $data['product'];
        }
        
        // Validation could be added here
        
        // Check if name exists
        if (Product::where('name', $data['name'])->exists()) {
             return response()->json([
                'success' => false,
                'message' => 'المنتج موجود مسبقاً',
                'products' => $this->getProductsList()
             ]);
        }

        $imagePath = null;
        if (isset($data['image']) && strpos($data['image'], 'data:image') === 0) {
            $imagePath = $this->saveImage($data['image']);
        }

        $product = Product::create([
            'name' => $data['name'],
            'hasSizes' => (int)($data['hasSizes'] ?? 0),
            'price' => $data['price'] ?? 0,
            's_price' => $data['s_price'] ?? 0,
            'm_price' => $data['m_price'] ?? 0,
            'l_price' => $data['l_price'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'barcode' => $data['barcode'] ?? null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'image' => $imagePath
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم إضافة المنتج بنجاح",
            'products' => $this->getProductsList()
        ]);
    }

    public function update(Request $request, $id)
    {
        // $id is from route, but legacy might send it in body.
        // If we use standard resource route: PUT /api/products/{id}
        // But legacy uses POST /api/products with action='update' and product data.
        // We will define a route that handles this, probably standardizing to PUT/POST.
        // For now let's assume standard Laravel controller method signature but we might accept ID from body if needed.
        
        $data = $request->input('product');
        if (!$data) {
            $data = $request->all();
            if(isset($data['product'])) $data = $data['product'];
        }

        $product = Product::find($data['id'] ?? $id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $imagePath = $product->image;
        if (isset($data['image']) && strpos($data['image'], 'data:image') === 0) {
            $imagePath = $this->saveImage($data['image']);
        }

        $product->update([
            'name' => $data['name'],
            'hasSizes' => (int)($data['hasSizes'] ?? 0),
            'price' => $data['price'] ?? 0,
            's_price' => $data['s_price'] ?? 0,
            'm_price' => $data['m_price'] ?? 0,
            'l_price' => $data['l_price'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'barcode' => $data['barcode'] ?? null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'image' => $imagePath
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم تحديث المنتج بنجاح",
            'products' => $this->getProductsList()
        ]);
    }

    public function destroy(Request $request, $id)
    {
        // Legacy used POST with action='delete' and id in body.
        // Laravel Resource would use DELETE /api/products/{id}.
        // We can support both if we route correctly.
        
        $deleteId = $id;
        if ($request->has('id')) {
            $deleteId = $request->input('id');
        }

        Product::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => "تم حذف المنتج بنجاح",
            'products' => $this->getProductsList()
        ]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');
        switch ($action) {
            case 'add':
                return $this->store($request);
            case 'update':
                $id = $request->input('product.id') ?? $request->input('id');
                // If ID is inside product array
                if(!$id && $request->has('product')) {
                    $prod = $request->input('product');
                    $id = $prod['id'] ?? null;
                }
                return $this->update($request, $id);
            case 'delete':
                return $this->destroy($request, $request->input('id'));
            case 'list':
                return $this->index();
            default:
                return response()->json(['success' => false, 'message' => 'Action not found']);
        }
    }

    private function saveImage($base64Image)
    {
        $image_parts = explode(";base64,", $base64Image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = uniqid() . '.' . $image_type;
        
        // Ensure directory exists
        $path = public_path('uploads/products');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        file_put_contents($path . '/' . $fileName, $image_base64);
        return 'uploads/products/' . $fileName;
    }

    private function getProductsList()
    {
        return Product::with('category_info')->get()->map(function ($product) {
            $data = $product->toArray();
            $data['category'] = $product->category_info ? $product->category_info->name : $product->category;
            return $data;
        });
    }
}
