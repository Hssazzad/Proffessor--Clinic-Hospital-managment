@php
  // Map surgery prescription data to canonical format
  $rx = (object) [
    'chief_complaint' => $prescription->surgery_name ?? '',
    'diagnosis'       => collect($diagnoses ?? [])->pluck('diagnosis_name')->implode("\n"),
    'advice'          => $prescription->instructions ?? '',
    'doctor_name'     => $prescription->doctor_name ?? '',
    'prescribed_on'   => $prescription->surgery_date ?? $prescription->created_at ?? now(),
    'created_at'      => $prescription->created_at ?? now(),
  ];

  $patient = (object) [
    'patientcode'  => $prescription->patient_code ?? '',
    'patientname'  => $prescription->patient_name ?? '',
    'mobile_no'    => $prescription->patient_phone ?? '',
    'age'          => ($prescription->patient_age ?? '') . '/' . ($prescription->patient_gender ?? ''),
  ];

  $rxDoctors = collect();
  $complaints = collect();
  $notes = [
    ['title' => 'Surgery', 'body' => 'Surgery: ' . ($prescription->surgery_name ?? '') . "\nSurgeon: " . ($prescription->surgeon_name ?? '')],
  ];
  if (trim((string) ($rx->advice ?? '')) !== '') {
    $notes[] = ['title' => 'Advice', 'body' => $rx->advice];
  }
@endphp
@include('print.prescription', [
  'rx'           => $rx,
  'rxDoctors'    => $rxDoctors,
  'patient'      => $patient,
  'complaints'   => $complaints,
  'diagnoses'    => collect(),
  'investigations' => $investigations ?? collect(),
  'medicines'    => collect($medicines ?? [])->map(function ($m) {
    return (object) [
      'name'       => $m->medicine_name ?? '',
      'strength'   => $m->strength ?? '',
      'morning'    => $m->morning ?? '',
      'noon'       => $m->noon ?? '',
      'night'      => $m->night ?? '',
      'timing'     => $m->timing ?? '',
      'duration'   => $m->duration ?? '',
      'note'       => $m->dose ?? '',
    ];
  }),
  'notes'        => $notes,
])
