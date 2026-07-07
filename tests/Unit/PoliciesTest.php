<?php

namespace Tests\Unit;

use App\Models\Shift;
use App\Policies\CategoryPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseInvoicePolicy;
use App\Policies\ShiftPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PoliciesTest extends TestCase
{
    public function test_product_policy_allows_managers_only(): void
    {
        $policy = new ProductPolicy();

        $this->assertTrue($policy->manage($this->actingAsAdmin()));
        $this->assertTrue($policy->manage($this->actingAsManager()));
        $this->assertFalse($policy->manage($this->actingAsCashier()));
    }

    public function test_category_policy_allows_managers_only(): void
    {
        $policy = new CategoryPolicy();

        $this->assertTrue($policy->manage($this->actingAsAdmin()));
        $this->assertTrue($policy->manage($this->actingAsManager()));
        $this->assertFalse($policy->manage($this->actingAsCashier()));
    }

    public function test_purchase_invoice_policy_allows_managers_only(): void
    {
        $policy = new PurchaseInvoicePolicy();

        $this->assertTrue($policy->viewAny($this->actingAsAdmin()));
        $this->assertTrue($policy->create($this->actingAsManager()));
        $this->assertFalse($policy->viewAny($this->actingAsCashier()));
        $this->assertFalse($policy->create($this->actingAsCashier()));
    }

    public function test_employee_policy_allows_everyone_to_list_but_only_admin_to_manage(): void
    {
        $policy = new EmployeePolicy();

        $this->assertTrue($policy->viewAny($this->actingAsCashier()));
        $this->assertTrue($policy->manage($this->actingAsAdmin()));
        $this->assertFalse($policy->manage($this->actingAsManager()));
    }

    public function test_shift_policy_rules(): void
    {
        $policy = new ShiftPolicy();
        $manager = $this->actingAsManager();
        $cashier = $this->actingAsCashier();
        $shift = Shift::create([
            'employee_id' => $cashier->id,
            'opened_at' => now(),
            'opening_float' => 0,
            'status' => 'open',
        ]);

        $this->assertTrue($policy->viewAny($manager));
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertTrue($policy->close($cashier, $shift));
        $this->assertTrue($policy->view($cashier, $shift));
        $this->assertFalse($policy->view($this->actingAsCashier(), $shift));
    }

    public function test_view_reports_gate_allows_managers_only(): void
    {
        $this->assertTrue(Gate::forUser($this->actingAsAdmin())->allows('viewReports'));
        $this->assertTrue(Gate::forUser($this->actingAsManager())->allows('viewReports'));
        $this->assertFalse(Gate::forUser($this->actingAsCashier())->allows('viewReports'));
    }
}
