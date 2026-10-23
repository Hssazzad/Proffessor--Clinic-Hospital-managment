<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RxMedicineAjaxTest extends TestCase
{
    use RefreshDatabase;

    private int $prescriptionId;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('medicine_types')->insert([
            'type_name'  => 'Unknown',
            'active'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('companies')->insert([
            'name'       => 'General',
            'active'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $patientId = DB::table('patients')->insertGetId([
            'patientcode' => 'P-TEST-0001',
            'patientname' => 'Test Patient',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->prescriptionId = (int) DB::table('prescriptions')->insertGetId([
            'patient_id'      => $patientId,
            'prescription_no' => 'RX-TEST-0001',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function addMed(string $name, string $strength = '')
    {
        return $this->post(route('rx.med.ajax'), [
            'action'          => 'add',
            'prescription_id' => $this->prescriptionId,
            'name'            => $name,
            'strength'        => $strength,
        ])->assertOk();
    }

    public function test_rx_med_ajax_route_is_bound_to_the_canonical_controller(): void
    {
        $route = Route::getRoutes()->getByName('rx.med.ajax');

        $this->assertNotNull($route, 'rx.med.ajax route must exist exactly once.');
        $this->assertStringEndsWith('PrescriptionWizardController@ajaxMedicine', $route->getActionName());
    }

    public function test_it_adds_a_medicine_row(): void
    {
        $json = $this->addMed('Napa Extra', '500mg')->json();

        $this->assertTrue($json['ok']);
        $this->assertFalse($json['duplicate'] ?? false);

        $this->assertDatabaseHas('prescriptions_medicine', [
            'id'              => $json['id'],
            'prescription_id' => $this->prescriptionId,
            'name_normalized' => 'napa extra',
        ]);
    }

    public function test_duplicate_add_returns_the_existing_row_without_inserting(): void
    {
        $first  = $this->addMed('Napa Extra', '500mg')->json();
        $second = $this->addMed('NAPA   extra', '500mg')->json(); // case/whitespace-insensitive

        $this->assertTrue($second['ok']);
        $this->assertTrue($second['duplicate']);
        $this->assertSame($first['id'], $second['id']);

        $this->assertDatabaseCount('prescriptions_medicine', 1);
    }

    public function test_same_medicine_with_different_strength_is_not_a_duplicate(): void
    {
        $this->addMed('Napa Extra', '500mg');
        $this->addMed('Napa Extra', '665mg');

        $this->assertDatabaseCount('prescriptions_medicine', 2);
    }

    public function test_quick_add_rejects_missing_strength(): void
    {
        $formId = $this->seedDosageForm('Tablet');

        $this->postJson(route('ajax.quick.add.medicine'), [
            'medicine_name'  => 'Ace',
            'dosage_form_id' => $formId,
        ])->assertStatus(422);

        $this->assertDatabaseMissing('common_medicine', ['name' => 'Ace']);
    }

    public function test_quick_add_creates_and_returns_the_new_medicine(): void
    {
        $formId = $this->seedDosageForm('Tablet');

        $json = $this->postJson(route('ajax.quick.add.medicine'), [
            'medicine_name'  => 'Ace',
            'strength'       => '500mg',
            'dosage_form_id' => $formId,
        ])->assertOk()->json();

        $this->assertTrue($json['ok']);
        $this->assertFalse($json['duplicate']);

        $this->assertDatabaseHas('common_medicine', [
            'id'              => $json['medicine']['id'],
            'name'            => 'Ace',
            'strength'        => '500mg',
            'name_normalized' => 'ace',
        ]);
    }

    private function seedDosageForm(string $name): int
    {
        return (int) DB::table('dosage_forms')->insertGetId([
            'dosage_name' => $name,
            'active'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
