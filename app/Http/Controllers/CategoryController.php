<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Categories fetched successfully',
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->input('category');
        if (!$data) $data = $request->all();

        $category = Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#000000'
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم إضافة الفئة بنجاح",
            'categories' => Category::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->input('category');
        if (!$data) $data = $request->all();

        $category = Category::find($data['id'] ?? $id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found']);
        }

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? $category->color
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم تحديث الفئة بنجاح",
            'categories' => Category::all()
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $deleteId = $id;
        if ($request->has('id')) $deleteId = $request->input('id');

        Category::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => "تم حذف الفئة بنجاح",
            'categories' => Category::all()
        ]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');
        switch ($action) {
            case 'add':
                return $this->store($request);
            case 'update':
                $id = $request->input('category.id') ?? $request->input('id');
                return $this->update($request, $id);
            case 'delete':
                return $this->destroy($request, $request->input('id'));
            default:
                return response()->json(['success' => false, 'message' => 'Action not found']);
        }
    }
}
