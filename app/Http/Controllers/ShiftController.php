<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use App\Services\ShiftService;
use App\Support\AuthUser;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $shiftService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Shift::class);

        $shifts = Shift::with('employee')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json([
            'status' => 'success',
            'shifts' => $shifts,
        ]);
    }

    public function current(Request $request)
    {
        $user = $request->user();
        $employeeId = AuthUser::employeeId($user) ?? $request->integer('employee_id');

        if (!$employeeId) {
            return response()->json([
                'status' => 'success',
                'shift' => null,
            ]);
        }

        $shift = Shift::with('employee')
            ->where('employee_id', $employeeId)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        return response()->json([
            'status' => 'success',
            'shift' => $shift,
        ]);
    }

    public function open(Request $request)
    {
        $this->authorize('open', Shift::class);

        $data = $request->validate([
            'opening_float' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $employeeId = AuthUser::employeeId($user);

        if (!$employeeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'فتح الوردية متاح للموظفين فقط',
            ], 422);
        }

        $existing = Shift::where('employee_id', $employeeId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'success',
                'message' => 'لديك وردية مفتوحة بالفعل',
                'shift' => $existing,
            ]);
        }

        $shift = Shift::create([
            'employee_id' => $employeeId,
            'opened_at' => now(),
            'opening_float' => $data['opening_float'],
            'status' => 'open',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم فتح الوردية بنجاح',
            'shift' => $shift->load('employee'),
        ]);
    }

    public function close(Request $request, Shift $shift)
    {
        $this->authorize('close', $shift);

        if ($shift->status === 'closed') {
            return response()->json([
                'status' => 'error',
                'message' => 'الوردية مغلقة بالفعل',
            ], 422);
        }

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $expectedCash = $this->shiftService->calculateExpectedCash($shift);
        $actualCash = (float) $data['actual_cash'];

        $shift->update([
            'closed_at' => now(),
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'cash_difference' => $actualCash - $expectedCash,
            'status' => 'closed',
            'notes' => $data['notes'] ?? $shift->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إغلاق الوردية بنجاح',
            'shift' => $shift->fresh()->load('employee'),
            'report' => $this->shiftService->buildReport($shift),
        ]);
    }

    public function report(Shift $shift)
    {
        $this->authorize('view', $shift);

        return response()->json([
            'status' => 'success',
            'report' => $this->shiftService->buildReport($shift),
        ]);
    }
}
