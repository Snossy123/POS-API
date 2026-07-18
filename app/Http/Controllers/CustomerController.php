<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->orderBy('name');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address_notes', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب العملاء بنجاح',
            'customers' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'unique:customers,phone'],
            'address_notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::create([
            'name' => trim($data['name']),
            'phone' => $data['phone'],
            'address_notes' => isset($data['address_notes']) ? trim((string) $data['address_notes']) : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة العميل بنجاح',
            'customer' => $customer,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:40',
                Rule::unique('customers', 'phone')->ignore($customer->id),
            ],
            'address_notes' => ['nullable', 'string'],
        ]);

        $customer->update([
            'name' => trim($data['name']),
            'phone' => $data['phone'],
            'address_notes' => isset($data['address_notes']) ? trim((string) $data['address_notes']) : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث العميل بنجاح',
            'customer' => $customer->fresh(),
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->salesInvoices()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف عميل لديه فواتير مرتبطة',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف العميل بنجاح',
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\s+/', '', trim($phone)) ?: trim($phone);
    }
}
