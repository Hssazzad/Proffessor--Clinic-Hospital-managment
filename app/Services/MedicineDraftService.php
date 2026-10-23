<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * MedicineDraftService
 *
 * Draft-based medicine management for the admission workflow.
 *
 * Instead of writing template medicines into the patient_*_medicines tables
 * the moment a medicine page is opened, every stage works on a per-patient,
 * per-admission TEMPORARY DRAFT held in the session:
 *
 *   1. Page open  -> a draft is rebuilt from the baseline: already-saved rows,
 *                   or the template when nothing is saved yet. A browser refresh
 *                   therefore discards unsaved edits. Nothing is written to the
 *                   database.
 *   2. Add / Edit / Delete  -> only mutate the session draft.
 *   3. Save All   -> the draft is written to the patient table and the session
 *                   draft is cleared. Rows removed from the draft are
 *                   soft-deleted (active = 0) so history is preserved.
 *   4. "Load Template Medicines" -> re-imports template rows that are not
 *                   already active (or already present in the draft).
 *
 * The same flow is used for every order type: instant, admit, preorder,
 * postorder, round and fresh. Patient-level actions never modify
 * template_medicine (template edits happen in Template Management only).
 */
class MedicineDraftService
{
    /**
     * Per-order-type configuration.
     *
     * - table         : patient_*_medicines table holding saved rows
     * - identity      : column that links a row to the patient
     *                   (admit/instant use patientcode, the others patientid)
     * - template_order: order_type value used when reading template_medicine
     * - has_round_no  : whether the table carries a round_no column
     *
     * @var array<string, array<string, mixed>>
     */
    public const TYPES = [
        'instant'  => ['table' => 'patient_instant_medicines',     'identity' => 'patientcode', 'template_order' => 'instant',   'has_round_no' => false],
        'admit'    => ['table' => 'patient_admission_medicines',   'identity' => 'patientcode', 'template_order' => 'admit',     'has_round_no' => false],
        'preorder' => ['table' => 'patient_presurgery_medicines',  'identity' => 'patientid',   'template_order' => 'preorder',  'has_round_no' => false],
        'postorder'=> ['table' => 'patient_postsurgery_medicines', 'identity' => 'patientid',   'template_order' => 'postorder', 'has_round_no' => false],
        'round'    => ['table' => 'patient_round_medicines',       'identity' => 'patientid',   'template_order' => 'postorder', 'has_round_no' => true],
        'fresh'    => ['table' => 'patient_fresh_medicines',       'identity' => 'patientid',   'template_order' => 'fresh',     'has_round_no' => false],
    ];

    /** Unique session key per order type / patient / admission. */
    public function sessionKey(string $orderType, string $patientKey, string $admissionId): string
    {
        return 'medicine_draft_' . $orderType . '_' . $patientKey . '_' . $admissionId;
    }

    /** Configuration for one order type. */
    public function config(string $orderType): array
    {
        if (!isset(self::TYPES[$orderType])) {
            abort(500, "Unknown medicine order type: {$orderType}");
        }

        return self::TYPES[$orderType];
    }

    /**
     * Read the current session draft (empty collection when no draft exists yet).
     */
    public function list(string $orderType, string $patientKey, string $admissionId): Collection
    {
        $key = $this->sessionKey($orderType, $patientKey, $admissionId);

        return collect(Session::get($key, []));
    }

    /** Store a draft (an array of draft items). */
    public function store(string $orderType, string $patientKey, string $admissionId, array $items): void
    {
        Session::put($this->sessionKey($orderType, $patientKey, $admissionId), array_values($items));
    }

    /** Remove the session draft (used after Save All). */
    public function clear(string $orderType, string $patientKey, string $admissionId): void
    {
        Session::forget($this->sessionKey($orderType, $patientKey, $admissionId));
    }

    /**
     * Build a fresh draft from the baseline:
     *  - already-saved active rows for this patient/admission are the source
     *    of truth and are used as-is;
     *  - on the first open (no saved rows) the template_medicine rows for this
     *    order type are used.
     *
     * Called on every page open, so a browser refresh before Save All rebuilds
     * the draft from the baseline (unsaved edits are discarded). Template rows
     * are never merged into an already-saved prescription automatically (see
     * loadTemplate() for the explicit button).
     */
    public function rebuild(string $orderType, string $patientKey, string $admissionId, string $templateId, ?int $roundNo = null): Collection
    {
        $config = $this->config($orderType);
        $table  = $config['table'];
        $identity = $config['identity'];

        $saved = $this->savedRows($config, $identity, $patientKey, $admissionId, $orderType);

        if ($saved->isNotEmpty()) {
            $items = $saved->map(fn ($row) => $this->toDraftItem($row, 'saved', $orderType, $roundNo));
        } else {
            $templates = DB::table('template_medicine')
                ->where('templeteid', $templateId)
                ->where('order_type', $config['template_order'])
                ->where('active', 1)
                ->orderBy('id')
                ->get();

            $items = $templates->map(fn ($row) => $this->toDraftItem($row, 'template', $orderType, $roundNo));
        }

        $items = $items->values();

        $this->store($orderType, $patientKey, $admissionId, $items->all());

        return $items;
    }

    /**
     * Active, saved rows for the patient / admission / order type.
     *
     * The round page historically lists every active row for the
     * patient/admission (round and postorder alike), so no order_type filter
     * is applied there. Every other order type is filtered by its own value.
     */
    protected function savedRows(array $config, string $identity, string $patientKey, string $admissionId, ?string $orderType = null): Collection
    {
        $query = DB::table($config['table'])
            ->where($identity, $patientKey)
            ->where('admissionid', $admissionId)
            ->where('active', 1)
            ->orderBy('id');

        if ($orderType && !$config['has_round_no']) {
            $query->where('order_type', $orderType);
        }

        return $query->get();
    }

    /**
     * Add a new medicine to the draft or update an existing draft item.
     *
     * @return array{status: string, item: object|null}
     */
    public function addOrUpdate(string $orderType, string $patientKey, string $admissionId, array $input, ?string $key = null, ?int $roundNo = null): array
    {
        $items = $this->list($orderType, $patientKey, $admissionId)->all();
        $data  = $this->mapInput($input);

        if (!empty($key)) {
            foreach ($items as $i => $item) {
                if ($item->key === $key) {
                    if ($this->findDuplicate($items, $data, $key)) {
                        return ['status' => 'duplicate', 'item' => null];
                    }

                    $item = $this->mergeInput($item, $data, $orderType, $roundNo);
                    $items[$i] = $item;
                    $this->store($orderType, $patientKey, $admissionId, $items);

                    return ['status' => 'updated', 'item' => $item];
                }
            }
        }

        if ($this->findDuplicate($items, $data, $key)) {
            return ['status' => 'duplicate', 'item' => null];
        }

        $item = $this->newDraftItem($data, $orderType, $roundNo);
        $items[] = $item;
        $this->store($orderType, $patientKey, $admissionId, $items);

        return ['status' => 'added', 'item' => $item];
    }

    /**
     * Remove a medicine from the draft. This never touches the database.
     */
    public function remove(string $orderType, string $patientKey, string $admissionId, string $key): bool
    {
        $items = $this->list($orderType, $patientKey, $admissionId)->all();
        $found = false;

        foreach ($items as $i => $item) {
            if ($item->key === $key) {
                unset($items[$i]);
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->store($orderType, $patientKey, $admissionId, array_values($items));
        }

        return $found;
    }

    /**
     * "Load Template Medicines" button.
     *
     * Imports template_medicine rows for this order type that are neither
     * already active in the database nor already present in the draft.
     * Returns the number of medicines imported.
     */
    public function loadTemplate(string $orderType, string $patientKey, string $admissionId, string $templateId, ?int $roundNo = null): int
    {
        $config = $this->config($orderType);
        $table  = $config['table'];
        $identity = $config['identity'];

        $activeSigs = $this->savedRows($config, $identity, $patientKey, $admissionId, $orderType)
            ->map(fn ($row) => $this->signature($row))
            ->all();

        $items = $this->list($orderType, $patientKey, $admissionId)->all();
        $seen  = array_merge($activeSigs, array_map(fn ($item) => $this->signature($item), $items));

        $templates = DB::table('template_medicine')
            ->where('templeteid', $templateId)
            ->where('order_type', $config['template_order'])
            ->where('active', 1)
            ->orderBy('id')
            ->get();

        $added = 0;

        foreach ($templates as $row) {
            $sig = $this->signature($row);
            if (in_array($sig, $seen, true)) {
                continue;
            }

            $items[] = $this->toDraftItem($row, 'template', $orderType, $roundNo);
            $seen[] = $sig;
            $added++;
        }

        if ($added > 0) {
            $this->store($orderType, $patientKey, $admissionId, $items);
        }

        return $added;
    }

    /**
     * True when the patient/admission already has saved rows for this order
     * type (used to decide whether previous-admission rows should be loaded).
     */
    public function hasSavedRows(string $orderType, string $patientKey, string $admissionId): bool
    {
        $config = $this->config($orderType);

        return $this->savedRows($config, $config['identity'], $patientKey, $admissionId, $orderType)
            ->isNotEmpty();
    }

    /**
     * Load the patient's medicines from previous admission(s) into the current
     * draft. Rows are imported as NEW draft items (no original id), so Save All
     * inserts them into the current admission without ever touching the
     * previous admission's records.
     *
     * Latest previous admission comes first, original row order is kept within
     * each admission. When nothing is found, the passed fallback draft is
     * returned untouched.
     */
    public function loadFromPreviousAdmission(string $orderType, string $patientKey, string $currentAdmissionId, Collection $fallback): Collection
    {
        $config   = $this->config($orderType);
        $table    = $config['table'];
        $identity = $config['identity'];

        $prevAdmissions = DB::table($table)
            ->where($identity, $patientKey)
            ->where('admissionid', '!=', $currentAdmissionId)
            ->where('active', 1)
            ->select('admissionid', DB::raw('MAX(id) as max_id'))
            ->groupBy('admissionid')
            ->orderByDesc('max_id')
            ->pluck('admissionid');

        $previous = collect();

        foreach ($prevAdmissions as $prevAdmissionId) {
            $query = DB::table($table)
                ->where($identity, $patientKey)
                ->where('admissionid', $prevAdmissionId)
                ->where('active', 1);

            if (!$config['has_round_no']) {
                $query->where('order_type', $orderType);
            }

            $previous = $previous->merge($query->orderBy('id')->get());
        }

        if ($previous->isEmpty()) {
            return $fallback;
        }

        $items = $previous->map(function ($row) use ($orderType) {
            $item = $this->toDraftItem($row, 'new', $orderType);
            $item->key = 'prev:' . $row->id;

            return $item;
        });

        $this->store($orderType, $patientKey, $currentAdmissionId, $items->all());

        return $items;
    }

    /**
     * Save All — persist the session draft to the patient table inside a
     * transaction, then clear the session draft.
     *
     * - existing saved rows still in the draft  -> updated
     * - template / new rows in the draft         -> inserted
     * - saved rows removed from the draft        -> soft-deleted (active = 0)
     *
     * @return array{inserted: int, updated: int, removed: int}
     */
    public function persist(string $orderType, string $patientKey, string $admissionId, ?int $roundNo = null): array
    {
        $config = $this->config($orderType);
        $table  = $config['table'];
        $identity = $config['identity'];
        $hasRoundNo = $config['has_round_no'];
        $roundNo = $roundNo ?? 1;

        $items = $this->list($orderType, $patientKey, $admissionId)->all();
        $now   = now();

        $result = ['inserted' => 0, 'updated' => 0, 'removed' => 0];

        DB::transaction(function () use ($config, $table, $identity, $patientKey, $admissionId, $orderType, $hasRoundNo, $roundNo, $items, $now, &$result) {
            // Active rows that existed BEFORE this save. Only these are soft-
            // delete candidates — freshly inserted rows must never be removed.
            $existing = DB::table($table)
                ->where($identity, $patientKey)
                ->where('admissionid', $admissionId)
                ->where('active', 1);

            if (!$config['has_round_no']) {
                $existing->where('order_type', $orderType);
            }

            $existingIds = $existing->pluck('id')->map(fn ($id) => (int) $id);

            $keptIds = [];

            foreach ($items as $item) {
                $row = $this->toDbRow($item, $orderType, $patientKey, $admissionId, $roundNo);
                $row['active'] = 1;
                $row['updated_at'] = $now;

                if ($item->source === 'saved' && !empty($item->id)) {
                    DB::table($table)->where('id', $item->id)->update($row);
                    $keptIds[] = (int) $item->id;
                    $result['updated']++;
                    continue;
                }

                if ($hasRoundNo && !empty($row['round_no'])) {
                    $row['round_no'] = $roundNo;
                }

                $row['created_at'] = $now;
                DB::table($table)->insert($row);
                $result['inserted']++;
            }

            // Soft-delete pre-existing active rows no longer part of the final
            // draft (reversible, keeps prescription history intact).
            $removable = $existingIds->reject(fn ($id) => in_array($id, $keptIds, true));

            if ($removable->isNotEmpty()) {
                DB::table($table)
                    ->whereIn('id', $removable)
                    ->update(['active' => 0, 'updated_at' => $now]);
                $result['removed'] = $removable->count();
            }
        });

        $this->clear($orderType, $patientKey, $admissionId);

        return $result;
    }

    /* ------------------------------------------------------------------ */
    /*  Internal helpers                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Turn a database row or template row into a draft item.
     */
    protected function toDraftItem(object $row, string $source, string $orderType, ?int $roundNo = null): object
    {
        return (object) [
            'key'         => $source === 'saved' ? 'saved:' . $row->id : 'tpl:' . $row->id,
            'source'      => $source,
            'id'          => $source === 'saved' ? (int) $row->id : null,
            'round_no'    => $row->round_no ?? $roundNo ?? 1,
            'templeteid'  => $row->templeteid ?? '',
            'group'       => $row->group ?? '',
            'name'        => $row->name ?? '',
            'strength'    => $row->strength ?? '',
            'dose'        => $row->dose ?? '',
            'morning'     => $row->morning ?? '',
            'noon'        => $row->noon ?? '',
            'night'       => $row->night ?? '',
            'route'       => $row->route ?? '',
            'frequency'   => $row->frequency ?? '',
            'duration'    => $row->duration ?? '',
            'timing'      => $row->timing ?? '',
            'meal_timing' => $row->meal_timing ?? '',
            'instruction' => $row->instruction ?? '',
            'company'     => $row->company ?? '',
            'note'        => $row->note ?? '',
            'order_type'  => $orderType,
            'active'      => 1,
        ];
    }

    /**
     * Build a draft item for a medicine typed into the form.
     */
    protected function newDraftItem(array $data, string $orderType, ?int $roundNo = null): object
    {
        return (object) array_merge([
            'key'        => 'new:' . uniqid('', true),
            'source'     => 'new',
            'id'         => null,
            'round_no'   => $roundNo ?? 1,
            'order_type' => $orderType,
            'active'     => 1,
        ], $data);
    }

    /**
     * Copy a draft item's editable fields onto a new version after an edit.
     */
    protected function mergeInput(object $item, array $data, string $orderType, ?int $roundNo = null): object
    {
        $merged = (object) array_merge((array) $item, $data);

        $merged->order_type = $orderType;
        $merged->active = 1;
        $merged->round_no = $roundNo ?? $merged->round_no;

        return $merged;
    }

    /**
     * Pick only the known medicine columns from a request payload.
     */
    protected function mapInput(array $input): array
    {
        return [
            'templeteid'  => $input['templeteid']  ?? '',
            'group'       => $input['group']       ?? '',
            'name'        => $input['name']        ?? '',
            'strength'    => $input['strength']    ?? '',
            'dose'        => $input['dose']        ?? '',
            'morning'     => $input['morning']     ?? '',
            'noon'        => $input['noon']        ?? '',
            'night'       => $input['night']       ?? '',
            'route'       => $input['route']       ?? '',
            'frequency'   => $input['frequency']   ?? '',
            'duration'    => $input['duration']    ?? '',
            'timing'      => $input['timing']      ?? '',
            'meal_timing' => $input['meal_timing'] ?? '',
            'instruction' => $input['instruction'] ?? '',
            'company'     => $input['company']     ?? '',
            'note'        => $input['note']        ?? '',
        ];
    }

    /**
     * Convert a draft item into a row ready for the patient table.
     */
    protected function toDbRow(object $item, string $orderType, string $patientKey, string $admissionId, ?int $roundNo): array
    {
        $config = $this->config($orderType);

        return [
            $config['identity'] => $patientKey,
            'admissionid' => $admissionId,
            'templeteid'  => $item->templeteid ?? '',
            'group'       => $item->group       ?? null,
            'name'        => $item->name        ?? '',
            'strength'    => $item->strength    ?? null,
            'dose'        => $item->dose        ?? null,
            'morning'     => $item->morning     ?? null,
            'noon'        => $item->noon        ?? null,
            'night'       => $item->night       ?? null,
            'route'       => $item->route       ?? null,
            'frequency'   => $item->frequency   ?? null,
            'duration'    => $item->duration    ?? null,
            'timing'      => $item->timing      ?? null,
            'meal_timing' => $item->meal_timing ?? null,
            'instruction' => $item->instruction ?? null,
            'company'     => $item->company     ?? null,
            'note'        => $item->note        ?? null,
            'order_type'  => $orderType,
        ] + ($config['has_round_no']
            ? ['round_no' => $item->round_no ?? $roundNo ?? 1]
            : []);
    }

    /**
     * Duplicate check across draft items (case-insensitive name/strength/dose),
     * ignoring the item currently being edited.
     */
    protected function findDuplicate(array $items, array $data, ?string $excludeKey = null): ?object
    {
        $target = $this->signature($data);

        foreach ($items as $item) {
            if ($excludeKey && $item->key === $excludeKey) {
                continue;
            }

            if ($this->signature($item) === $target) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Normalize a medicine into a "name|strength|dose" signature.
     */
    protected function signature(object|array $medicine): string
    {
        if (is_object($medicine)) {
            $name     = $medicine->name     ?? '';
            $strength = $medicine->strength ?? '';
            $dose     = $medicine->dose     ?? '';
        } else {
            $name     = $medicine['name']     ?? '';
            $strength = $medicine['strength'] ?? '';
            $dose     = $medicine['dose']     ?? '';
        }

        return strtolower(trim((string) $name)) . '|'
            . strtolower(trim((string) $strength)) . '|'
            . strtolower(trim((string) $dose));
    }
}
