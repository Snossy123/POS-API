<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب بيانات الموظفين بنجاح',
            'employees' => $employees // or $employees->toArray()
        ]);
    }

    public function store(Request $request)
    {
        // Legacy sends { action: 'add', employee: {...} }
        $data = $request->input('employee');
        if (!$data) $data = $request->all(); // Fallback if direct

        // Validation - could verify unique email
        if(Employee::where('email', $data['email'])->exists()){
             // Legacy didn't explicitly return error for duplicate email but DB would throw.
             // We can return nice error.
             return response()->json(['success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقاً']);
        }

        $employee = Employee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Storing plain text as per legacy?! 
            // Step 60 showed: VALUES (?, ?, ?, ...) with plain password.
            // Ideally we should hash it, but for compatibility with legacy login (if any), we might need to keep it plain OR hash it if we are updating login too.
            // User asked to "convert". I should keep behavior or improve it.
            // Improved: Hash it. But then existing login script (if external) might break.
            // I'll keep it as is for now but add a TODO.
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'salary' => $data['salary'] ?? 0,
            'hiring_date' => $data['hiring_date'] ?? null,
            'active' => !empty($data['active']) ? 1 : 0
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم إضافة الموظف بنجاح",
            'employees' => $this->getEmployeesList()
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->input('employee');
        if (!$data) $data = $request->all();

        $employee = Employee::find($data['id'] ?? $id);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'موظف غير موجود']);
        }

        $employee->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'salary' => $data['salary'] ?? 0,
            'hiring_date' => $data['hiring_date'] ?? null,
            'active' => !empty($data['active']) ? 1 : 0
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم تحديث بيانات الموظف بنجاح",
            'employees' => $this->getEmployeesList()
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $deleteId = $id;
        if ($request->has('id')) $deleteId = $request->input('id');

        Employee::destroy($deleteId);

        return response()->json([
            'success' => true,
            'message' => "تم حذف الموظف بنجاح",
            'employees' => $this->getEmployeesList()
        ]);
    }

    public function handle(Request $request)
    {
        $action = $request->input('action');
        switch ($action) {
            case 'add':
                return $this->store($request);
            case 'update':
                $id = $request->input('employee.id') ?? $request->input('id');
                return $this->update($request, $id);
            case 'delete':
                return $this->destroy($request, $request->input('id'));
            default:
                return response()->json(['success' => false, 'message' => 'Action not found']);
        }
    }

    private function getEmployeesList()
    {
        return Employee::orderBy('id', 'desc')->get();
    }
}
