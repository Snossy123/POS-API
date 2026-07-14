<?php

namespace App\Http\Controllers;

use App\Models\Modifier;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    public function index()
    {
        $modifiers = Modifier::with('categories:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Modifier $modifier) => $this->transform($modifier));

        return response()->json([
            'status' => 'success',
            'message' => 'Modifiers fetched successfully',
            'modifiers' => $modifiers,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Modifier::class);

        $data = $request->input('modifier') ?? $request->all();

        $modifier = Modifier::create([
            'name' => $data['name'],
            'price' => (float) ($data['price'] ?? 0),
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : true,
        ]);

        if (isset($data['category_ids']) && is_array($data['category_ids'])) {
            $modifier->categories()->sync(array_map('intval', $data['category_ids']));
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الإضافة بنجاح',
            'modifiers' => $this->allTransformed(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage', Modifier::class);

        $data = $request->input('modifier') ?? $request->all();
        $modifier = Modifier::find($data['id'] ?? $id);

        if (!$modifier) {
            return response()->json(['success' => false, 'message' => 'الإضافة غير موجودة']);
        }

        $modifier->update([
            'name' => $data['name'] ?? $modifier->name,
            'price' => array_key_exists('price', $data) ? (float) $data['price'] : $modifier->price,
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : $modifier->active,
        ]);

        if (isset($data['category_ids']) && is_array($data['category_ids'])) {
            $modifier->categories()->sync(array_map('intval', $data['category_ids']));
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإضافة بنجاح',
            'modifiers' => $this->allTransformed(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage', Modifier::class);

        $deleteId = $request->input('id') ?? $id;
        Modifier::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإضافة بنجاح',
            'modifiers' => $this->allTransformed(),
        ]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');

        return match ($action) {
            'add' => $this->store($request),
            'update' => $this->update($request, $request->input('modifier.id') ?? $request->input('id')),
            'delete' => $this->destroy($request, $request->input('id')),
            default => response()->json(['success' => false, 'message' => 'Action not found']),
        };
    }

    private function allTransformed(): array
    {
        return Modifier::with('categories:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Modifier $modifier) => $this->transform($modifier))
            ->values()
            ->all();
    }

    private function transform(Modifier $modifier): array
    {
        return [
            'id' => $modifier->id,
            'name' => $modifier->name,
            'price' => (float) $modifier->price,
            'active' => (bool) $modifier->active,
            'category_ids' => $modifier->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'categories' => $modifier->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->values()->all(),
        ];
    }
}
