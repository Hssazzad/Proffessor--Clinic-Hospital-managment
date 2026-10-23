<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AcctGltypeInfo;
use App\Models\Accounting\AcctTblBalsheetAccount;
use App\Models\CollectionGroup;
use App\Models\CollectionGroupAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionGroupController extends Controller
{
    /**
     * GL cash ledger. A collection group credits income/asset heads, so the cash
     * account itself must never be attachable — crediting cash against cash is
     * what the MCR action's `not_in` rule already blocks at save time.
     */
    private function cashAccountNo(): string
    {
        return (string) config('accounting.cash_account', '205101101');
    }

    public function index()
    {
        $groups = CollectionGroup::with('accounts.account')
            ->orderBy('code')
            ->get();

        // Deliberately the SAME account set the Multiple Cash Receive dropdown
        // offers: that screen (TransactionController::multipleCashReceive)
        // excludes only the cash account and its `exists` rule accepts any
        // AccountNo, so filtering by gl_type here would be stricter than the
        // screen that ultimately validates the posting.
        $accounts = AcctTblBalsheetAccount::where('AccountNo', '!=', $this->cashAccountNo())
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group', 'gl_type']);

        // Convenience filter only; never a restriction.
        $glTypes = AcctGltypeInfo::orderBy('code')->get(['code', 'name']);

        // A group can outlive an account (acct_tbl_balsheet_account is legacy and
        // renumberable), so surface codes that no longer resolve instead of
        // letting the edit form silently drop them on save.
        $knownCodes = $accounts->pluck('AccountNo')->map(fn ($no) => (string) $no)->all();
        $missingCodes = [];
        foreach ($groups as $group) {
            $missingCodes[$group->id] = array_values(array_filter(
                $group->accounts->map(fn ($link) => (string) $link->account_code)->all(),
                fn ($code) => ! in_array($code, $knownCodes, true)
            ));
        }

        return view('settings.collection.index', compact('groups', 'accounts', 'glTypes', 'missingCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'      => ['required', 'string', 'max:50', 'unique:collection_groups,code'],
            'name'      => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'in:0,1'],
            'accounts'  => ['nullable', 'array'],
            'accounts.*' => ['nullable', 'string', 'exists:acct_tbl_balsheet_account,AccountNo'],
        ]);

        $group = DB::transaction(function () use ($request, $validated) {
            $group = CollectionGroup::create([
                'code'      => trim($validated['code']),
                'name'      => trim($validated['name']),
                'is_active' => $request->input('is_active', 1) ? 1 : 0,
            ]);

            $this->syncAccounts($group, $validated['accounts'] ?? []);

            return $group;
        });

        return redirect()->route('settings.collection')
            ->with('success', 'Collection Group "' . $group->code . ' — ' . $group->name . '" created successfully.');
    }

    public function update(Request $request, CollectionGroup $collectionGroup)
    {
        $validated = $request->validate([
            'code'      => ['required', 'string', 'max:50', 'unique:collection_groups,code,' . $collectionGroup->id],
            'name'      => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'in:0,1'],
            'accounts'  => ['nullable', 'array'],
            'accounts.*' => ['nullable', 'string', 'exists:acct_tbl_balsheet_account,AccountNo'],
        ]);

        DB::transaction(function () use ($request, $validated, $collectionGroup) {
            $collectionGroup->update([
                'code'      => trim($validated['code']),
                'name'      => trim($validated['name']),
                'is_active' => $request->input('is_active', 1) ? 1 : 0,
            ]);

            $this->syncAccounts($collectionGroup, $validated['accounts'] ?? []);
        });

        return redirect()->route('settings.collection')
            ->with('success', 'Collection Group "' . $collectionGroup->code . '" updated successfully.');
    }

    public function destroy(CollectionGroup $collectionGroup)
    {
        $code = $collectionGroup->code;
        $collectionGroup->delete();

        return redirect()->route('settings.collection')
            ->with('success', 'Collection Group "' . $code . '" deleted successfully.');
    }

    /**
     * Replaces the group's account list. Order follows the submitted sequence so
     * the receive screen renders rows in the order the admin arranged them.
     * Duplicates are collapsed against the unique (group, account_code) index.
     */
    private function syncAccounts(CollectionGroup $group, array $accountCodes): void
    {
        $accountCodes = array_values(array_unique(array_filter(
            array_map('trim', $accountCodes),
            fn ($code) => $code !== ''
        )));

        $group->accounts()->delete();

        foreach ($accountCodes as $position => $code) {
            $group->accounts()->create([
                'account_code' => $code,
                'sort_order'   => $position + 1,
            ]);
        }
    }
}