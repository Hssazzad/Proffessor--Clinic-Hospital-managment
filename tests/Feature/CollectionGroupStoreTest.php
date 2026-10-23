<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\CollectionGroupAccount;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CollectionGroupStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    public function test_creating_a_group_with_accounts_works()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $response = $this->actingAs($user)->post('/settings/collection/store', [
                'code'      => 'TST-901',
                'name'      => 'Store Path Group',
                'is_active' => '1',
                'accounts'  => ['40601101', '50101103', '50102101'],
            ]);

            $response->assertRedirect('/settings/collection');
            $response->assertSessionHas('success');

            $group = CollectionGroup::where('code', 'TST-901')->first();
            $this->assertNotNull($group);
            $this->assertEquals('Store Path Group', $group->name);
            $this->assertTrue((bool) $group->is_active);

            // syncAccounts must preserve the submitted order as sort_order.
            $links = CollectionGroupAccount::where('collection_group_id', $group->id)
                ->orderBy('sort_order')
                ->get();
            $this->assertCount(3, $links);
            $this->assertEquals(
                ['40601101', '50101103', '50102101'],
                $links->pluck('account_code')->all()
            );
            $this->assertEquals([1, 2, 3], $links->pluck('sort_order')->all());
        } finally {
            DB::rollBack();
        }
    }

    public function test_creating_an_inactive_group_works()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $response = $this->actingAs($user)->post('/settings/collection/store', [
                'code'      => 'TST-902',
                'name'      => 'Store Inactive Group',
                'is_active' => '0',
                'accounts'  => ['40601101'],
            ]);

            $response->assertRedirect('/settings/collection');
            $response->assertSessionHas('success');

            $group = CollectionGroup::where('code', 'TST-902')->first();
            $this->assertNotNull($group);
            $this->assertFalse((bool) $group->is_active);
        } finally {
            DB::rollBack();
        }
    }

    public function test_removing_one_account_detaches_it_from_the_database()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $group = CollectionGroup::create(['code' => 'TST-905', 'name' => 'Detach Group', 'is_active' => 1]);
            $group->accounts()->createMany([
                ['account_code' => '40601101', 'sort_order' => 1],
                ['account_code' => '50101103', 'sort_order' => 2],
                ['account_code' => '50102101', 'sort_order' => 3],
            ]);
            $this->assertCount(3, $group->accounts()->get());

            // The right panel posts only the two codes still in it.
            $response = $this->actingAs($user)->put('/settings/collection/update/' . $group->id, [
                'code'      => 'TST-905',
                'name'      => 'Detach Group',
                'is_active' => '1',
                'accounts'  => ['40601101', '50102101'],
            ]);
            $response->assertRedirect('/settings/collection');

            $links = CollectionGroupAccount::where('collection_group_id', $group->id)
                ->orderBy('sort_order')
                ->get();

            $this->assertCount(2, $links);
            $this->assertEquals(['40601101', '50102101'], $links->pluck('account_code')->all());
            $this->assertEquals(
                0,
                CollectionGroupAccount::where('collection_group_id', $group->id)
                    ->where('account_code', '50101103')
                    ->count(),
                'The removed account must be detached, not just hidden.'
            );
        } finally {
            DB::rollBack();
        }
    }

    public function test_updating_a_group_account_list_replaces_it_in_submitted_order()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $group = CollectionGroup::create(['code' => 'TST-903', 'name' => 'Update Group', 'is_active' => 1]);
            $group->accounts()->createMany([
                ['account_code' => '10901101', 'sort_order' => 1],
                ['account_code' => '20701101', 'sort_order' => 2],
            ]);

            $response = $this->actingAs($user)->put('/settings/collection/update/' . $group->id, [
                'code'      => 'TST-903',
                'name'      => 'Update Group Renamed',
                'is_active' => '1',
                'accounts'  => ['50102101', '40601101'],
            ]);

            $response->assertRedirect('/settings/collection');
            $response->assertSessionHas('success');

            $links = CollectionGroupAccount::where('collection_group_id', $group->id)
                ->orderBy('sort_order')
                ->get();
            $this->assertEquals(['50102101', '40601101'], $links->pluck('account_code')->all());
        } finally {
            DB::rollBack();
        }
    }

    public function test_store_rejects_an_unknown_account()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $response = $this->actingAs($user)->post('/settings/collection/store', [
                'code'     => 'TST-904',
                'name'     => 'Bad Account Group',
                'accounts' => ['99999999'],
            ]);

            $response->assertSessionHasErrors('accounts.0');
            $this->assertNull(CollectionGroup::where('code', 'TST-904')->first());
        } finally {
            DB::rollBack();
        }
    }
}