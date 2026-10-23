/* =========================================================================
   PROFESSOR CLINIC — DISCHARGE BOOKLET PRINT TYPOGRAPHY
   Legal landscape · 2 half-pages per sheet · fills entire page
   ========================================================================= */

@page {
    size: legal landscape;
    margin: 2mm !important;
}

@media print {
    .booklet-sheet { border: 3px double #000 !important; }
    .booklet-page { border: none !important; border-right: 3px double #000 !important; }
    .booklet-page:last-child { border-right: none !important; border-left: 3px double #000 !important; }

    html, body {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    .wrapper, .content-wrapper, .main-sidebar, .main-header, .content, .container, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
}

.booklet-sheet {
    display: table;
    table-layout: fixed;
    width: 355.6mm !important;
    max-width: 355.6mm !important;
    height: 215.5mm !important;
    margin: 0 !important;
    padding: 4mm !important;
    box-sizing: border-box !important;
    page-break-after: always;
    page-break-inside: avoid;
    border: 3px double #000;
    border-collapse: separate;
    border-spacing: 0;
}
.booklet-sheet:last-child {
    page-break-after: auto;
}

.booklet-page {
    display: table-cell;
    width: 50% !important;
    max-width: 50% !important;
    min-width: 50% !important;
    height: 100%;
    box-sizing: border-box;
    padding: 8mm 6mm 6mm;
    border: none;
    border-right: 3px double #000;
    vertical-align: top;
    font-family: "Segoe UI", "Hind Siliguri", "Noto Sans Bengali", Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.45;
    color: #000;
    text-align: left;
    position: relative;
}
.booklet-page:last-child {
    border-right: none;
    border-left: 3px double #000;
}

/* Back cover — spread content down the page */
.b-back .b-note { min-height: 110pt; }
.b-back .b-info { margin: 0 0 6mm; }
.b-back .b-feature { font-size: 13.5pt; line-height: 2.1; margin: 0 0 4mm; }
.b-back .b-feature li { padding: 6pt 0; }

/* Content top + signature/footer bottom pin */
.b-page-fill {
    display: table;
    width: 100%;
    height: 100%;
}
.b-page-top-row { display: table-row; }
.b-page-top { display: table-cell; vertical-align: top; }
.b-page-bottom-row { display: table-row; height: 1px; }
.b-page-bottom {
    display: table-cell;
    vertical-align: bottom;
    padding-top: 6mm;
}

/* 1. Hospital masthead -------------------------------------------------- */
.b-hos-header {
    display: table;
    width: 100%;
    margin-bottom: 1.5mm;
}
.b-hos-logo,
.b-hos-details {
    display: table-cell;
    vertical-align: middle;
}
.b-hos-logo {
    width: 24mm;
    padding-right: 3mm;
}
.b-hos-logo img {
    display: block;
    max-width: 20mm;
    max-height: 20mm;
}
.b-hos-name {
    font-size: 20pt;
    line-height: 1.15;
    font-weight: 700;
    text-align: left;
    margin: 0 0 1.5mm;
}
.b-hos-addr {
    font-size: 11pt;
    text-align: left;
    margin: 1.5mm 0 0;
}
.b-hos-phone {
    font-size: 9.5pt;
    text-align: left;
    margin: 1mm 0 0;
}
.b-thinrule {
    border-top: 1.4pt solid #000;
    margin: 2mm 0 3mm;
}

/* 2. Document & section titles ------------------------------------------ */
.b-title {
    font-size: 14pt;
    line-height: 1.2;
    font-weight: 700;
    text-align: center;
    background: #000;
    color: #fff;
    padding: 4.5pt 7pt;
    margin: 0 0 3mm;
}
.b-subtitle {
    font-weight: 700;
    font-size: 12.5pt;
    line-height: 1.25;
    margin: 4mm 0 2.2mm;
    text-decoration: underline;
}

/* 3. Labels & value boxes ------------------------------------------------ */
.b-label {
    font-weight: 700;
    font-size: 11.5pt;
    white-space: nowrap;
}
.b-value {
    border: 1.2pt solid #000;
    min-height: 30pt;
    padding: 4pt 6pt;
    font-size: 11pt;
    line-height: 1.4;
    margin-bottom: 2mm;
    page-break-inside: avoid;
}
.b-value.b-min2 { min-height: 42pt; }
.b-value.b-min3 { min-height: 54pt; }
.b-note {
    border: 1.2pt solid #000;
    min-height: 34pt;
    padding: 4pt 6pt;
    font-size: 11pt;
    line-height: 1.4;
    margin-bottom: 2mm;
    page-break-inside: avoid;
}
.b-note.b-min4 { min-height: 60pt; }

/* 4. Patient info grid --------------------------------------------------- */
.b-info {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0 0 4mm;
}
.b-info td {
    border: 1pt solid #000;
    padding: 4pt 6pt;
    vertical-align: middle;
    font-size: 11pt;
}
.b-info .lbl {
    width: 22%;
    font-weight: 700;
    background: #fff;
    white-space: nowrap;
    line-height: 1.3;
}
.b-info .val {
    width: 28%;
    font-weight: 600;
}

/* 5. Operation grid ------------------------------------------------------- */
.b-opgrid {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 4mm;
}
.b-opgrid td {
    padding: 8pt 9pt;
    vertical-align: middle;
    border: 1.4pt solid #000;
}
.b-opgrid .lbl {
    width: 28%;
    font-weight: 700;
    font-size: 14pt;
    background: #fff;
    line-height: 1.4;
}
.b-opgrid .val {
    font-weight: 600;
    font-size: 15pt;
    text-align: center;
    min-height: 36pt;
    padding: 8pt 9pt;
}
.b-opgrid .val-wide {
    font-weight: 600;
    font-size: 13pt;
    text-align: center;
    min-height: 64pt;
    padding: 8pt 9pt;
    vertical-align: top;
}
.b-opgrid .val-date {
    width: 25%;
    font-weight: 600;
    font-size: 13pt;
    text-align: center;
    padding: 8pt 9pt;
}

/* 6. Clinical content sections ------------------------------------------- */
.b-section {
    margin-bottom: 5.5mm;
    page-break-inside: avoid;
}
.b-section-label {
    font-weight: 700;
    font-size: 11pt;
    margin-bottom: 2mm;
}
.b-section-box {
    border: 1pt solid #000;
    padding: 8pt 10pt;
    min-height: 34pt;
    font-size: 11pt;
    line-height: 1.55;
}
.b-section-box.tall   { min-height: 60pt; }
.b-section-box.taller { min-height: 95pt; }

.b-row2 {
    display: table;
    width: 100%;
    table-layout: fixed;
}
.b-row2 > .b-col {
    display: table-cell;
    vertical-align: top;
    padding-right: 6mm;
}
.b-row2 > .b-col:last-child {
    padding-right: 0;
    padding-left: 6mm;
}

.b-highlight {
    border: 1.5pt solid #000;
    background: #f2f2f2;
    padding: 8pt 12pt;
    font-size: 12pt;
    font-weight: 700;
    text-align: center;
    margin: 4mm 0 6mm;
}

.b-cert-text {
    font-size: 11.5pt;
    line-height: 1.55;
    text-align: justify;
    margin: 2.2mm 0 4mm;
}
.b-cert-text .blank {
    display: inline-block;
    min-width: 20mm;
    border-bottom: 1pt solid #000;
    text-align: center;
    font-weight: 700;
}

.b-certificate {
    margin: 4mm 0 0;
    padding: 4mm 5mm 2mm;
    border-top: 1pt solid #000;
    border-bottom: 1pt solid #000;
    font-size: 15.5pt;
    line-height: 1.85;
}
.b-certificate-line {
    margin: 0 0 3.5mm;
    white-space: normal;
}
.b-certificate-line:last-child { margin-bottom: 0; }
.b-certificate-centered { text-align: center; }
.b-field-gap { display: inline-block; width: 5mm; }
.b-cover-signatures {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6mm;
}
.b-cover-signatures td {
    width: 50%;
    vertical-align: top;
    padding: 0 8mm;
}
.b-cover-seal { text-align: left; }
.b-cover-sign { text-align: right; }
.b-sign-label {
    font-size: 9pt;
    font-weight: 700;
    display: inline-block;
    padding-bottom: 1mm;
    border-bottom: 1pt solid #000;
    white-space: nowrap;
}
.b-sign-space { padding-top: 2mm; }

/* 7. Medicine list -------------------------------------------------------- */
.b-med {
    width: 100%;
    border-collapse: collapse;
    font-size: 10.5pt;
    margin-bottom: 5.5mm;
    page-break-inside: avoid;
}
.b-med th,
.b-med td {
    border: 1.2pt solid #000;
    padding: 6.5pt 8pt;
    vertical-align: middle;
    line-height: 1.45;
}
.b-med th {
    font-weight: 700;
    font-size: 10.5pt;
    text-align: center;
    background: #eee;
}
.b-c { text-align: center; }
.b-w1 { width: 6%; }
.b-w3 { width: 10.5%; }
.b-empty {
    border: 1px dashed #000;
    min-height: 46pt;
    padding: 8pt 10pt;
    font-size: 11pt;
    text-align: center;
    margin-bottom: 4.5mm;
}

.b-advice-list {
    margin: 0 0 5.5mm;
    padding-left: 9mm;
    font-size: 11pt;
    line-height: 1.85;
}
.b-advice-list li {
    margin-bottom: 2.2mm;
}

/* 8. Photo box (real passport size) --------------------------------------- */
.b-photo-cell {
    width: 34mm;
    vertical-align: top;
    padding: 0;
}
.b-photo {
    width: 30mm;
    height: 36mm;
    border: 1px solid #000;
    font-size: 10pt;
    font-weight: 700;
    text-align: center;
    color: #444;
}
.b-photo img {
    width: 29mm;
    height: 35mm;
    padding: 0.5mm;
}
.ph-label {
    display: block;
    padding-top: 13mm;
}

/* 9. Features list -------------------------------------------------------- */
.b-feature {
    list-style: none;
    margin: 0 0 4mm;
    padding: 0;
    font-size: 13pt;
    line-height: 1.75;
}
.b-feature li {
    display: inline-block;
    width: 49%;
    vertical-align: top;
    padding: 3pt 0;
}

/* 10. Signature & seal ---------------------------------------------------- */
.b-sign-row {
    display: table;
    width: 100%;
    table-layout: fixed;
    margin-top: 4.5mm;
}
.b-sign-cell {
    display: table-cell;
    vertical-align: bottom;
    width: 50%;
}
.b-sign-cell:last-child {
    text-align: right;
}
.b-sign-block {
    margin-top: 5mm;
    text-align: right;
    page-break-inside: avoid;
}
.b-sign-line {
    font-size: 10.5pt;
    font-weight: 700;
    margin-bottom: 2mm;
    text-align: right;
}
.b-sign-box {
    display: inline-block;
    width: 56mm;
    height: 16mm;
    border: 1.2pt solid #000;
}
.b-seal-box {
    display: inline-block;
    width: 48mm;
    height: 16mm;
    border: 1.2pt solid #000;
}
.b-sign-caption {
    font-size: 9.5pt;
    font-weight: 700;
    text-align: center;
    padding-top: 2.2mm;
}

/* 11. Footer & acknowledgment --------------------------------------------- */
.b-foot {
    margin-top: 3mm;
    font-size: 8.5pt;
    text-align: center;
    border-top: 1.2pt solid #000;
    padding-top: 1.5mm;
    color: #333;
}
.b-ack {
    margin-top: 3mm;
    border: 1pt dashed #000;
    padding: 3mm 4mm;
    font-size: 10pt;
    text-align: center;
    page-break-inside: avoid;
}
.b-spacer {
    height: 18pt;
}
