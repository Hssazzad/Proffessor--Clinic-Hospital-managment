@php
    $admission = $admission ?? null;
    $resPatientCode = $patientcode ?? ($admission->patientcode ?? '');
    $templateId = $admission->templeteid ?? '';

    $dbCC = '';
    $dbInv = '';
    $dbOE = '';

    if ($templateId) {
        $templateComplains = \Illuminate\Support\Facades\DB::table('template_complain')
            ->where('templateid', $templateId)
            ->get();
        if ($templateComplains->isNotEmpty()) {
            $dbCC = $templateComplains->map(fn($c) => '• ' . $c->complain . ($c->note ? ' (' . $c->note . ')' : ''))->join("\n");
        }

        $templateInvestigations = \Illuminate\Support\Facades\DB::table('template_investigations')
            ->where('templateid', $templateId)
            ->get();
        if ($templateInvestigations->isNotEmpty()) {
            $dbInv = $templateInvestigations->map(fn($i) => '• ' . $i->name . ($i->note ? ' (' . $i->note . ')' : ''))->join("\n");
        }
    }

    if ($resPatientCode) {
        $nursingAdmission = \Illuminate\Support\Facades\DB::table('nursing_admissions')
            ->where('patient_code', $resPatientCode)
            ->orderBy('id', 'desc')
            ->first();
        if ($nursingAdmission) {
            $pulse = $nursingAdmission->pulse ?? '';
            $bp = $nursingAdmission->bp ?? '';
            $temp = $nursingAdmission->temp ?? $nursingAdmission->temperature ?? '';
            $weight = $nursingAdmission->weight ?? '';

            $oeParts = [];
            if ($pulse) $oeParts[] = 'Pulse: ' . $pulse;
            if ($bp) $oeParts[] = 'BP: ' . $bp;
            if ($temp) $oeParts[] = 'Temp: ' . $temp;
            if ($weight) $oeParts[] = 'Weight: ' . $weight;

            if (!empty($oeParts)) {
                $dbOE = implode("\n", $oeParts);
            }
        }
    }

    if (empty($dbOE)) {
        $dbOE = "Pulse: \nBP: ";
    }
@endphp
<script>window.admissionPrintDefaults = @json(['cc' => $dbCC, 'oe' => $dbOE, 'inv' => $dbInv]);</script>
@include('admission.partials.print_prescription_core')
