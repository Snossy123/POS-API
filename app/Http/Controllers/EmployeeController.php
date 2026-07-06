<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Support\AuthUser;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::orderBy('id', 'desc')->get();

        if (!AuthUser::isAdmin($request->user())) {
            $employees = $employees->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'active' => (bool) $employee->active,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب بيانات الموظفين بنجاح',
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Employee::class);

        $data = $request->input('employee') ?? $request->all();

        if (Employee::where('email', $data['email'])->exists()) {
            return response()->json(['success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقاً']);
        }

        $employee = Employee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'salary' => $data['salary'] ?? 0,
            'hiring_date' => $data['hiring_date'] ?? null,
            'active' => !empty($data['active']) ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الموظف بنجاح',
            'employees' => $this->getEmployeesList(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage', Employee::class);

        $data = $request->input('employee') ?? $request->all();
        $employee = Employee::find($data['id'] ?? $id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'موظف غير موجود']);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'salary' => $data['salary'] ?? 0,
            'hiring_date' => $data['hiring_date'] ?? null,
            'active' => !empty($data['active']) ? 1 : 0,
        ];

        $employee->fill($payload);
        if (!empty($data['password'])) {
            $employee->password = $data['password'];
        }
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الموظف بنجاح',
            'employees' => $this->getEmployeesList(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('manage', Employee::class);

        $deleteId = $request->input('id') ?? $id;
        Employee::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الموظف بنجاح',
            'employees' => $this->getEmployeesList(),
        ]);
    }

    public function handle(Request $request)
    {
        return match ($request->input('action')) {
            'add' => $this->store($request),
            'update' => $this->update($request, $request->input('employee.id') ?? $request->input('id')),
            'delete' => $this->destroy($request, $request->input('id')),
            default => response()->json(['success' => false, 'message' => 'Action not found']),
        };
    }

    private function getEmployeesList()
    {
        return Employee::orderBy('id', 'desc')->get();
    }
}
