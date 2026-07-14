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
            'categories' => $this->categoriesWithModifiers(),
        ]);
    }

    private function categoriesWithModifiers()
    {
        return Category::with(['modifiers' => function ($q) {
            $q->where('active', true)->orderBy('name');
        }])->get()->map(function (Category $category) {
            $data = $category->toArray();
            $data['modifiers'] = $category->modifiers->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'price' => (float) $m->price,
            ])->values()->all();
            unset($data['modifiers_relation']);
            return $data;
        })->values();
    }

    private function allCategoriesPayload()
    {
        return $this->categoriesWithModifiers();
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Category::class);

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
            'categories' => $this->allCategoriesPayload(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage', Category::class);

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

        if (isset($data['modifier_ids']) && is_array($data['modifier_ids'])) {
            $category->modifiers()->sync(array_map('intval', $data['modifier_ids']));
        }

        return response()->json([
            'success' => true,
            'message' => "تم تحديث الفئة بنجاح",
            'categories' => $this->allCategoriesPayload(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage', Category::class);

        $deleteId = $id;
        if ($request->has('id')) $deleteId = $request->input('id');

        Category::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => "تم حذف الفئة بنجاح",
            'categories' => $this->allCategoriesPayload(),
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
