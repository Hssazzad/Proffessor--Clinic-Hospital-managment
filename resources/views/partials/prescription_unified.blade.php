{{--
    Standalone printable prescription page.
    Delegates all data mapping to the print/prescription master template.
--}}
@include('print.prescription', [
    'rx'           => $rx ?? null,
    'rxDoctors'    => $rxDoctors ?? null,
    'patient'      => $patient ?? null,
    'complaints'   => $complaints ?? [],
    'diagnoses'    => $diagnoses ?? [],
    'investigations' => $investigations ?? [],
    'medicines'    => $medicines ?? [],
    'doctorName'   => '',
    'doctorLine'   => '',
    'doctorSub'    => '',
])
