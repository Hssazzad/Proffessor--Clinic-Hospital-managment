<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription</title>
    <style>
        @page {
            size: A4;
            margin: 3.5cm 1cm 3cm 1cm; /* top | right | bottom | left */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12.5pt;
            color: #222;
            line-height: 1.4;
        }

        /* Patient Info Bar */
        .patient-bar {
            width: 100%;
            border: 1px solid #555;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f0f2f5;
        }
        .patient-bar td {
            padding: 6px 10px;
            border: 1px solid #888;
            font-size: 12.5pt;
        }

        /* Main Layout */
        .main-layout {
            width: 100%;
            border-collapse: collapse;
        }
        .left-col {
            width: 18%;
            vertical-align: top;
            padding-right: 10px;
            font-size: 10.5pt;
        }
        .right-col {
            width: 82%;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            font-size: 12.5pt;
            margin-top: 6px;
            margin-bottom: 2px;
        }

        /* Rx Heading */
        .rx-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 6px;
            font-family: Georgia, serif;
        }

        /* Medicine Table */
        .med-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5pt;
        }
        .med-table th {
            background: #e0e4eb;
            border: 1px solid #555;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 11.5pt;
        }
        .med-table td {
            border: 1px solid #666;
            padding: 3px 4px;
            vertical-align: middle;
        }
        .med-table tr:nth-child(even) td {
            background: #f7f8fa;
        }
        .text-center {
            text-align: center;
        }
        .medicine-name {
            text-align: left;
            font-weight: 800;
            font-size: 12.5pt;
        }
    </style>
</head>
<body>

    {{-- ========== Patient Information ========== --}}
    <table class="patient-bar">
        <tr>
            <td><strong>Patient Code:</strong> {{ $patient->code ?? '' }}</td>
            <td><strong>Patient Name:</strong> {{ $patient->name ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Mobile:</strong> {{ $patient->mobile ?? '' }}</td>
            <td><strong>Date:</strong> {{ $date ?? date('d-m-Y') }}</td>
        </tr>
    </table>

    {{-- ========== Main Content ========== --}}
    <table class="main-layout">
        <tr>
          
            {{-- Left Side --}}
<td class="left-col">
    <div class="section-title">C/C:</div>
    <div style="white-space: pre-line;">{!! nl2br(e($cc ?? '')) !!}</div>

    <div class="section-title" style="margin-top:10px;">O/E:</div>
    <div style="white-space: pre-line;">{!! nl2br(e($oe ?? '')) !!}</div>

    <div class="section-title" style="margin-top:10px;">Inv:</div>
    <div style="white-space: pre-line;">{!! nl2br(e($inv ?? '')) !!}</div>
</td>

            {{-- Right Side: Medicine Table --}}
            <td class="right-col">

                <table class="med-table">
                    <thead>
                        <tr>
                            <th style="width:38%;">Medicine Name</th>
                            <th style="width:28%;">
                                When to take?<br>
                                <small>Morning | Noon | Night</small>
                            </th>
                            <th style="width:16%;">
                                Meal<br>
                                <small>Before | After</small>
                            </th>
                            <th style="width:18%;">
                                Duration<br>
                                <small>Day | Month | Continue</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicines as $med)
                            <tr>
                                <td class="medicine-name">
                                    {{ $med['name'] ?? '' }}
                                    @if(!empty($med['dose']))
                                        {{ $med['dose'] }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $med['when'] ?? '' }}
                                </td>
                                <td class="text-center">
                                    {{ $med['meal'] ?? '' }}
                                </td>
                                <td class="text-center">
                                    {{ $med['duration'] ?? '' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No medicine</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>