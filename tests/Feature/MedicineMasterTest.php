<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MedicineMasterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureUsersTable();

        $this->user = User::factory()->create();

        $this->ids['type'] = (int) DB::table('medicine_types')->insertGetId([
            'type_name'  => 'General',
            'active'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->ids['company'] = (int) DB::table('companies')->insertGetId([
            'name'       => 'Square Pharma',
            'active'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->ids['dosage'] = (int) DB::table('dosage_forms')->insertGetId([
            'dosage_name' => 'Tablet',
            'active'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'medicine_type_id' => $this->ids['type'],
            'company_id'       => $this->ids['company'],
            'dosage_form_id'   => $this->ids['dosage'],
            'medicine_name'    => 'Napa',
            'strength'         => '500mg',
            'note'             => 'Take after meal',
            'status'           => '1',
        ], $overrides);
    }

    public function test_index_route_is_bound_to_the_extracted_controller(): void
    {
        $route = Route::getRoutes()->getByName('settings.medicines.index');

        $this->assertNotNull($route, 'settings.medicines.index route must exist.');
        $this->assertStringEndsWith('MedicineMasterController@medicinesIndex', $route->getActionName());
    }

    public function test_store_creates_medicine_with_note_and_normalizes_name(): void
    {
        $this->actingAs($this->user)
            ->post(route('settings.medicines.store'), $this->storePayload())
            ->assertRedirect(route('settings.medicines.index'));

        $this->assertDatabaseHas('common_medicine', [
            'name'            => 'Napa',
            'name_normalized' => 'napa',
            'note'            => 'Take after meal',
            'strength'        => '500mg',
            'company'         => 'Square Pharma',
        ]);
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAs($this->user)
            ->from(route('settings.medicines.index'))
            ->post(route('settings.medicines.store'), ['medicine_name' => 'Napa'])
            ->assertSessionHasErrors(['medicine_type_id', 'company_id', 'dosage_form_id', 'status']);

        $this->assertDatabaseCount('common_medicine', 0);
    }

    public function test_store_blocks_case_and_whitespace_duplicate(): void
    {
        $this->actingAs($this->user)->post(route('settings.medicines.store'), $this->storePayload());

        $this->actingAs($this->user)
            ->from(route('settings.medicines.index'))
            ->post(route('settings.medicines.store'), $this->storePayload(['medicine_name' => 'NAPA']))
            ->assertSessionHasErrors(['medicine_name']);

        $this->assertDatabaseCount('common_medicine', 1);
    }

    public function test_update_allows_same_name_but_blocks_another_existing_name(): void
    {
        $this->actingAs($this->user)->post(route('settings.medicines.store'), $this->storePayload());
        $first = DB::table('common_medicine')->where('name', 'Napa')->first();

        // Same name (own row) → allowed.
        $this->actingAs($this->user)
            ->put(route('settings.medicines.update', $first->id), $this->storePayload(['strength' => '665mg']))
            ->assertRedirect();

        $this->assertDatabaseHas('common_medicine', ['id' => $first->id, 'strength' => '665mg']);

        // Create a second medicine and try to rename it to the first one's name.
        $this->actingAs($this->user)
            ->post(route('settings.medicines.store'), $this->storePayload(['medicine_name' => 'Ace', 'note' => null]));
        $second = DB::table('common_medicine')->where('name', 'Ace')->first();

        $this->actingAs($this->user)
            ->from(route('settings.medicines.index'))
            ->put(route('settings.medicines.update', $second->id), $this->storePayload(['medicine_name' => ' napa ']))
            ->assertSessionHasErrors(['medicine_name']);

        $this->assertDatabaseHas('common_medicine', ['id' => $second->id, 'name' => 'Ace']);
    }

    public function test_quick_add_creates_and_returns_page(): void
    {
        $json = $this->actingAs($this->user)
            ->postJson(route('settings.medicines.quick-add'), $this->storePayload(['medicine_name' => 'Seclo']))
            ->assertOk()->json();

        $this->assertTrue($json['ok']);
        $this->assertFalse($json['duplicate']);
        $this->assertSame('Seclo', $json['medicine']['name']);
        $this->assertArrayHasKey('page', $json);

        $this->assertDatabaseHas('common_medicine', ['name' => 'Seclo', 'name_normalized' => 'seclo']);
    }

    public function test_quick_add_flags_duplicate(): void
    {
        $this->actingAs($this->user)
            ->post(route('settings.medicines.store'), $this->storePayload());

        $json = $this->actingAs($this->user)
            ->postJson(route('settings.medicines.quick-add'), $this->storePayload(['medicine_name' => 'napa']))
            ->assertOk()->json();

        $this->assertTrue($json['ok']);
        $this->assertTrue($json['duplicate']);

        $this->assertDatabaseCount('common_medicine', 1);
    }

    public function test_check_duplicate_returns_exact_match(): void
    {
        $this->actingAs($this->user)->post(route('settings.medicines.store'), $this->storePayload());

        $json = $this->actingAs($this->user)
            ->getJson(route('settings.medicines.check-duplicate', ['name' => 'Napa']))
            ->assertOk()->json();

        $this->assertNotEmpty($json['matches']);
        $this->assertTrue(collect($json['matches'])->firstWhere('name', 'Napa')['exact']);
    }

    public function test_list_html_returns_rendered_fragment(): void
    {
        $this->actingAs($this->user)->post(route('settings.medicines.store'), $this->storePayload());

        $json = $this->actingAs($this->user)
            ->getJson(route('settings.medicines.list-html'))
            ->assertOk()->json();

        $this->assertTrue($json['ok']);
        $this->assertStringContainsString('Napa', $json['html']);
        $this->assertSame(1, $json['total']);
    }

    public function test_prescription_filtered_medicines_exposes_note(): void
    {
        $this->actingAs($this->user)->post(route('settings.medicines.store'), $this->storePayload());

        $data = $this->actingAs($this->user)
            ->getJson(route('ajax.filtered.medicines', ['medicine_type_id' => $this->ids['type']]))
            ->assertOk()->json();

        $row = collect($data)->firstWhere('name', 'Napa');
        $this->assertNotNull($row);
        $this->assertSame('Take after meal', $row['note']);
    }

    /**
     * The users table is legacy (no Laravel migration), so create it for the
     * in-memory test database to allow actingAs() on the auth-protected
     * settings routes.
     */
    private function ensureUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
