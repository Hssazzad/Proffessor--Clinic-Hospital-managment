<?php
/*
 * Shared prescription formatting helpers.
 *
 * The dose / duration / meal-timing parsing used to be duplicated between
 * partials/prescription_unified.blade.php (PHP) and
 * admission/partials/_print_prescription_js.blade.php (JS). This file is the
 * PHP side's single copy; partials/rx_page.blade.php pulls it in with:
 *
 *     $rxHelpers = include resource_path('views/partials/rx_helpers.php');
 *
 * Returns an array of closures. Bangla digits and Bangla timing words are
 * still understood on input (existing records contain them) — only the
 * rendered labels are English.
 */

$rxBanglaDigits = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];

/** Normalise a value to plain ASCII digits and single spaces. */
$rxNormalise = function ($value) use ($rxBanglaDigits) {
    $text = preg_replace('/\s+/u', ' ', trim((string) ($value ?? '')));
    return strtr($text, $rxBanglaDigits);
};

/** "1 tab (1/2)" -> "1/2", "0" -> "0", anything else passes through. */
$rxDoseValue = function ($value) use ($rxNormalise) {
    $text = $rxNormalise($value);
    if ($text === '') return '';
    if (preg_match('/\(1\/2\)|\b1\/2\b|\b0\.5\b|half|অর্ধ/ui', $text)) return '1/2';
    if (preg_match('/\(0\)|^0(\D|$)|চলবে/ui', $text)) return '0';
    if (preg_match('/\(1\)|^1(\D|$)/u', $text)) return '1';
    return $text;
};

/** Expand "1+0+1" / "bd" / "tds" style schedules into [morning, noon, night]. */
$rxSchedule = function ($schedule) use ($rxNormalise) {
    $text = mb_strtolower($rxNormalise($schedule));
    if ($text === '') return null;

    // Extract the schedule pattern from text like "oral · 1+0+1" or "1+0+1"
    if (preg_match('/[\d\/]/', $text) && preg_match('/[\+\-]/', $text)) {
        // Remove any non-essential characters and extract the pattern
        $pattern = preg_replace('/[^0-9+\-\/]/', '', $text);
        $parts = preg_split('/[\+\-]/', $pattern);
        return [$parts[0] ?? '', $parts[1] ?? '', $parts[2] ?? ''];
    }

    // Also try to match patterns like "1+0+1" directly from the text
    if (preg_match('/(\d+)\s*\+\s*(\d+)\s*\+\s*(\d+)/', $text, $matches)) {
        return [$matches[1] ?? '', $matches[2] ?? '', $matches[3] ?? ''];
    }

    $map = [
        'qds' => ['1', '1', '1'], 'qid' => ['1', '1', '1'],
        'tds' => ['1', '1', '1'], 'tid' => ['1', '1', '1'],
        'bds' => ['1', '0', '1'], 'bd'  => ['1', '0', '1'], 'bid' => ['1', '0', '1'],
        'od'  => ['1', '0', '0'], 'mane' => ['1', '0', '0'], 'daily' => ['1', '0', '0'],
        'hs'  => ['0', '0', '1'], 'nocte' => ['0', '0', '1'],
    ];
    foreach ($map as $key => $parts) {
        if (preg_match('/\b' . $key . '\b/', $text)) return $parts;
    }
    return null;
};

/** Split a duration into [days, months, continueTick]. */
$rxDurationSplit = function ($duration) use ($rxNormalise) {
    $text   = $rxNormalise($duration);
    $lower  = mb_strtolower($text);
    if ($lower === '') return ['', '', ''];

    if (preg_match('/চলবে|\bcont\b|\bcontinue\b|\bon-?going\b/u', $lower)) {
        return ['', '', '&#10003;'];
    }
    if (preg_match('/মাস|\bmonths?\b|\bmos?\b/u', $lower)) {
        $num = preg_match('/(\d+(?:\.\d+)?)/', $lower, $m) ? $m[1] : $text;
        return ['', $num, ''];
    }
    if (preg_match('/দিন|\bdays?\b/u', $lower)) {
        $num = preg_match('/(\d+(?:\.\d+)?)/', $lower, $m) ? $m[1] : $text;
        return [$num, '', ''];
    }
    $num = preg_match('/(\d+(?:\.\d+)?)/', $lower, $m) ? $m[1] : '';
    if ($num !== '') {
        return [$num, '', ''];
    }
    return [$text, '', ''];
};

$rxDuration = function ($duration) use ($rxNormalise) {
    $text   = $rxNormalise($duration);
    $lower  = mb_strtolower($text);
    $number = preg_match('/(\d+(?:\.\d+)?)/', $lower, $m) ? $m[1] : '';

    if (preg_match('/মাস|\bmonths?\b|\bmos?\b/u', $lower))         return ($number ?: $text) . ' মাস';
    if (preg_match('/দিন|\bdays?\b/u', $lower))                    return ($number ?: $text) . ' দিন';
    if ($number !== '')                                            return $number . ' দিন';
    return $text;
};

/** Decide the before/after meal ticks. */
$rxMeal = function ($timing, $instruction) {
    $text = mb_strtolower(trim((string) $timing . ' ' . (string) $instruction));
    if ($text === '') return ['', ''];

    $before = preg_match('/\bbefore\b|\bempty\b|\bac\b|\ba\.c\b|আগে|খালি/u', $text);
    $after  = preg_match('/\bafter\b|\bwith\b|\bpc\b|\bp\.c\b|পরে|সাথে/u', $text);

    if ($before && !$after) return ['&#10003;', ''];
    if ($after && !$before) return ['', '&#10003;'];
    return ['', ''];
};

/** Pad the table out so short prescriptions still fill the sheet. */
$rxBlankRows = function ($count) {
    $row = '<tr class="rx-empty-row">' . str_repeat('<td>&nbsp;</td>', 8) . '</tr>';
    return str_repeat($row, max(0, (int) $count));
};

/** Build the full <tbody> html for a medicine collection. */
$rxRows = function ($medicines, $minRows = 8) use ($rxDoseValue, $rxSchedule, $rxDuration, $rxMeal, $rxBlankRows) {
    $medicines = collect($medicines);
    $html      = '';

    foreach ($medicines as $m) {
        $m = is_array($m) ? (object) $m : $m;

        $morning = $m->morning ?? $m->sokal ?? '';
        $noon    = $m->noon ?? $m->afternoon ?? $m->dupure ?? $m->dupur ?? '';
        $night   = $m->night ?? $m->raat ?? '';

        if ($morning === '' && $noon === '' && $night === '') {
            // Try to parse from frequency, dose, or route field
            $sourceData = $m->frequency ?? $m->dose ?? $m->route ?? '';
            $parsed = $rxSchedule($sourceData);
            if ($parsed) [$morning, $noon, $night] = $parsed;
        }

        [$beforeMeal, $afterMeal]  = $rxMeal($m->timing ?? '', $m->note ?? $m->instruction ?? '');
        $durationText              = $rxDuration($m->duration ?? '');

        $name        = trim((string) ($m->name ?? $m->medicine_name ?? ''));
        $instruction = trim((string) ($m->instruction ?? $m->note ?? ''));
        $detail      = collect([$m->route ?? '', $m->dose ?? ''])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->implode(' · ');

        // If still no schedule found, try to extract from detail field
        if ($morning === '' && $noon === '' && $night === '' && $detail !== '') {
            $parsed = $rxSchedule($detail);
            if ($parsed) {
                [$morning, $noon, $night] = $parsed;
                // Remove the schedule pattern from detail if it was parsed successfully
                $detail = preg_replace('/\d+\s*\+\s*\d+\s*\+\s*\d+/', '', $detail);
                $detail = preg_replace('/[\d\/]+\s*[\+\-]\s*[\d\/]+\s*[\+\-]\s*[\d\/]+/', '', $detail);
                $detail = trim(preg_replace('/\s+/', ' ', $detail));
                $detail = trim($detail, ' ·');
            }
        }

        // Remove route from detail to keep medicine name clean
        $doseText = trim((string) ($m->dose ?? ''));
        // Remove schedule pattern from dose if it was parsed successfully
        if ($morning !== '' || $noon !== '' || $night !== '') {
            $doseText = preg_replace('/\d+\s*\+\s*\d+\s*\+\s*\d+/', '', $doseText);
            $doseText = preg_replace('/[\d\/]+\s*[\+\-]\s*[\d\/]+\s*[\+\-]\s*[\d\/]+/', '', $doseText);
            $doseText = trim(preg_replace('/\s+/', ' ', $doseText));
        }
        $detail = collect([$doseText])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->implode(' · ');

        $html .= '<tr>'
            . '<td class="rx-med-name">' . e($name)
            . (trim((string) ($m->strength ?? '')) !== '' ? '<span>' . e($m->strength) . '</span>' : '')
            . ($detail !== '' ? '<span>' . e($detail) . '</span>' : '')
            . '</td>'
            . '<td>' . (e($rxDoseValue($morning)) ?: '&nbsp;') . '</td>'
            . '<td>' . (e($rxDoseValue($noon)) ?: '&nbsp;') . '</td>'
            . '<td>' . (e($rxDoseValue($night)) ?: '&nbsp;') . '</td>'
            . '<td>' . ($beforeMeal ?: '&nbsp;') . '</td>'
            . '<td>' . ($afterMeal ?: '&nbsp;') . '</td>'
            . '<td>' . (e($durationText) ?: '&nbsp;') . '</td>'
            . '<td class="rx-med-instruction">' . ($instruction !== '' ? e($instruction) : '&nbsp;') . '</td>'
            . '</tr>';
    }

    return $html . $rxBlankRows($minRows - $medicines->count());
};

return [
    'normalise'     => $rxNormalise,
    'dose'          => $rxDoseValue,
    'schedule'      => $rxSchedule,
    'duration'      => $rxDuration,
    'durationSplit' => $rxDurationSplit,
    'meal'          => $rxMeal,
    'blankRows'     => $rxBlankRows,
    'rows'          => $rxRows,
];
