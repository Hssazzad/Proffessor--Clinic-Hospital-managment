<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Discharge Booklet</title>
    <style>
        @page { size: A3 landscape; margin: 0 !important; }
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; background: #fff; box-sizing: border-box; }
        * { box-sizing: border-box; }
        .booklet-sheet { display: table; table-layout: fixed; width: 100% !important; height: 100vh; margin: 0 !important; padding: 3mm !important; box-sizing: border-box !important; page-break-after: always; break-after: page; border-collapse: separate; border-spacing: 0; }
        .booklet-sheet:last-child { page-break-after: auto; break-after: auto; }
        .booklet-page { display: table-cell; width: 50% !important; height: 100%; box-sizing: border-box; padding: 6mm 8mm; border: 1.5pt solid #000; vertical-align: top; font-family: "Segoe UI", "Hind Siliguri", "Noto Sans Bengali", Arial, sans-serif; font-size: 13pt; line-height: 1.5; color: #000; text-align: left; position: relative; }
        .b-hos-name { font-size: 22pt; line-height: 1.15; font-weight: 700; text-align: center; letter-spacing: .6pt; margin: 0 0 1.5mm; }
        .b-hos-addr { font-size: 13pt; text-align: center; margin: 1.8mm 0 0; }
        .b-hos-phone { font-size: 11pt; text-align: center; margin: 1.2mm 0 0; }
        .b-thinrule { border-top: 1.4pt solid #000; margin: 3mm 0 4mm; }
        .b-title { font-size: 16pt; line-height: 1.2; font-weight: 700; text-align: center; letter-spacing: .7pt; background: #000; color: #fff; padding: 5.5pt 8pt; margin: 0 0 4mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .b-subtitle { font-weight: 700; font-size: 12.5pt; line-height: 1.25; margin: 4mm 0 2.2mm; text-decoration: underline; }
        .b-label { font-weight: 700; font-size: 11.5pt; white-space: nowrap; }
        .b-value { border: 1.2pt solid #000; min-height: 36pt; padding: 5.5pt 7pt; font-size: 12pt; line-height: 1.4; margin-bottom: 2.8mm; page-break-inside: avoid; break-inside: avoid; }
        .b-note { border: 1.2pt solid #000; min-height: 40pt; padding: 5.5pt 7pt; font-size: 12pt; line-height: 1.4; margin-bottom: 2.8mm; page-break-inside: avoid; break-inside: avoid; }
        .b-info { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 6mm; }
        .b-info td { border: 1pt solid #000; padding: 5.5pt 7pt; vertical-align: middle; font-size: 13pt; }
        .b-info .lbl { width: 22%; font-weight: 700; background: #eee; white-space: nowrap; }
        .b-info .val { width: 28%; font-weight: 600; }
        .b-opgrid { width: 100%; border-collapse: collapse; margin: 0 0 6mm; }
        .b-opgrid td { padding: 9pt 11pt; vertical-align: middle; border: 1.4pt solid #000; }
        .b-opgrid .lbl { width: 28%; font-weight: 700; font-size: 13pt; background: #fff; line-height: 1.45; }
        .b-opgrid .val { font-weight: 600; font-size: 14pt; text-align: center; min-height: 46pt; padding: 12pt 11pt; }
        .b-opgrid .val-wide { font-weight: 600; font-size: 12pt; text-align: center; min-height: 85pt; padding: 12pt 11pt; vertical-align: top; }
        .b-opgrid .val-date { width: 25%; font-weight: 600; font-size: 12pt; text-align: center; padding: 12pt 11pt; }
        .b-feature { list-style: none; margin: 0 0 5mm; padding: 0; font-size: 13pt; line-height: 1.95; }
        .b-feature li { display: block; padding: 3pt 0; }
        .b-sign-block { margin-top: 5mm; text-align: right; page-break-inside: avoid; break-inside: avoid; }
        .b-sign-line { font-size: 10.5pt; font-weight: 700; margin-bottom: 2mm; text-align: right; }
        .b-sign-box { display: inline-block; width: 56mm; height: 18mm; border: 1.2pt solid #000; }
        .b-seal-box { display: inline-block; width: 48mm; height: 18mm; border: 1.2pt solid #000; }
        .b-foot { margin-top: 3mm; font-size: 8.5pt; text-align: center; border-top: 1.2pt solid #000; padding-top: 1.5mm; color: #333; }
        .b-ack { margin-top: 3mm; border: 1pt dashed #000; padding: 3mm 4mm; font-size: 10pt; text-align: center; page-break-inside: avoid; break-inside: avoid; }
    </style>
</head>
<body>
    @include('admission.discharge.partials.discharge_booklet')
</body>
</html>
