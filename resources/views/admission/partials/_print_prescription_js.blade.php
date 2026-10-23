function admissionPrintEscape(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function admissionPrintDoseValue(value) {
    var text = String(value || '').replace(/\s+/g, ' ').trim();
    var digits = {
        '\u09E6': '0',
        '\u09E7': '1',
        '\u09E8': '2',
        '\u09E9': '3',
        '\u09EA': '4',
        '\u09EB': '5',
        '\u09EC': '6',
        '\u09ED': '7',
        '\u09EE': '8',
        '\u09EF': '9'
    };

    text = text.replace(/[\u09E6-\u09EF]/g, function (digit) {
        return digits[digit] || digit;
    });

    if (!text) return '';
    if (/\(1\/2\)|\b1\/2\b|\b0\.5\b|\u0985\u09B0\u09CD\u09A7/.test(text)) return '1/2';
    if (/\(0\)|^0(\D|$)|\u099A\u09B2\u09AC\u09C7/.test(text)) return '0';
    if (/\(1\)|^1(\D|$)/.test(text)) return '1';

    return text;
}

function admissionPrintDurationColumns(duration) {
    var text = String(duration || '').replace(/\s+/g, ' ').trim();
    var digits = {
        '\u09E6': '0',
        '\u09E7': '1',
        '\u09E8': '2',
        '\u09E9': '3',
        '\u09EA': '4',
        '\u09EB': '5',
        '\u09EC': '6',
        '\u09ED': '7',
        '\u09EE': '8',
        '\u09EF': '9'
    };

    text = text.replace(/[\u09E6-\u09EF]/g, function (digit) {
        return digits[digit] || digit;
    });

    var lower = text.toLowerCase();
    var number = (lower.match(/(\d+(?:\.\d+)?)/) || [])[1] || '';
    var day = '';
    var month = '';
    var ongoing = '';

    if (lower.includes('\u099A\u09B2\u09AC\u09C7') || lower.includes('continue') || lower.includes('ongoing')) {
        ongoing = '&#10003;';
    } else if (lower.includes('\u09AE\u09BE\u09B8') || lower.includes('month') || lower.includes(' mo')) {
        month = number || text;
    } else if (lower.includes('\u09A6\u09BF\u09A8') || lower.includes('day')) {
        day = number || text;
    } else if (number) {
        day = number;
    } else if (text) {
        day = text;
    }

    return {
        day: admissionPrintEscape(day),
        month: admissionPrintEscape(month),
        ongoing: ongoing
    };
}

function admissionPrintMealColumns(timing, instruction) {
    var text = (String(timing || '') + ' ' + String(instruction || '')).toLowerCase();
    var before = '';
    var after = '';

    if (
        text.includes('before') ||
        text.includes('empty') ||
        text.includes('ac') ||
        text.includes('\u0986\u0997\u09C7') ||
        text.includes('\u0996\u09BE\u09B2\u09BF')
    ) {
        before = '&#10003;';
    }
    
    if (
        text.includes('after') ||
        text.includes('with') ||
        text.includes('pc') ||
        text.includes('\u09AA\u09B0\u09C7') ||
        text.includes('\u09B8\u09BE\u09A5\u09C7')
    ) {
        after = '&#10003;';
    }

    return {
        before: before,
        after: after
    };
}

function admissionPrintMedicineRow(medicine, morning, noon, night, strength, timing, duration, instruction) {
    var medicineText = admissionPrintEscape(medicine);
    var strengthText = admissionPrintEscape(strength);
    var morningText = admissionPrintEscape(admissionPrintDoseValue(morning));
    var noonText = admissionPrintEscape(admissionPrintDoseValue(noon));
    var nightText = admissionPrintEscape(admissionPrintDoseValue(night));
    var mealColumns = admissionPrintMealColumns(timing, instruction);
    var durationText = admissionPrintEscape(duration || '');
    var instructionText = admissionPrintEscape(instruction || '');

    return '<tr>' +
        '<td class="rx-med-name">' + medicineText + (strengthText ? '<span>' + strengthText + '</span>' : '') + '</td>' +
        '<td>' + morningText + '</td>' +
        '<td>' + noonText + '</td>' +
        '<td>' + nightText + '</td>' +
        '<td>' + (mealColumns.before || '&nbsp;') + '</td>' +
        '<td>' + (mealColumns.after || '&nbsp;') + '</td>' +
        '<td>' + (durationText || '&nbsp;') + '</td>' +
        '<td class="rx-med-instruction">' + (instructionText || '&nbsp;') + '</td>' +
    '</tr>';
}

function admissionPrintBlankMedicineRows(count) {
    var rows = '';

    for (var i = 0; i < count; i++) {
        rows += '<tr class="rx-empty-row">' +
            '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>' +
            '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>' +
        '</tr>';
    }

    return rows;
}

function admissionPrintPrescriptionHtml(config) {
    config = config || {};

    var defaults = window.admissionPrintDefaults || {};
    var title = admissionPrintEscape(config.title || 'Prescription');
    var stageTitle = admissionPrintEscape(config.stageTitle || title);
    var patientCode = admissionPrintEscape(config.patientCode || '');
    var patientName = admissionPrintEscape(config.patientName || '');
    var mobile = admissionPrintEscape(config.mobile || '');
    var admissionDate = admissionPrintEscape(config.admissionDate || '');
    var logo = admissionPrintEscape(config.logoUrl || '');
    var rows = config.rows || admissionPrintBlankMedicineRows(8);
    var today = admissionPrintEscape(new Date().toLocaleDateString('en-GB'));
    var doctorName = admissionPrintEscape(config.doctorName || '');
    var doctorSpeciality = admissionPrintEscape(config.doctorSpeciality || '');
    var doctorRegNo = admissionPrintEscape(config.doctorRegNo || '');
    var doctorContact = admissionPrintEscape(config.doctorContact || '');
    var doctorMeta = [];

    if (doctorRegNo) doctorMeta.push('Reg: ' + doctorRegNo);
    if (doctorContact) doctorMeta.push('Mobile: ' + doctorContact);

    var oeDefaultObj = { Pulse: '', BP: '', Temp: '', Weight: '' };
    var rawOe = config.oe || defaults.oe || '';
    if (rawOe) {
        var lines = rawOe.split('\n');
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (!line) continue;
            if (line.indexOf(':') !== -1) {
                var parts = line.split(':');
                var k = parts[0].trim();
                var v = parts.slice(1).join(':').trim();
                var found = false;
                for (var defK in oeDefaultObj) {
                    if (defK.toLowerCase() === k.toLowerCase()) {
                        oeDefaultObj[defK] = v;
                        found = true;
                        break;
                    }
                }
                if (!found) oeDefaultObj[k] = v;
            } else {
                oeDefaultObj[line] = '';
            }
        }
    }
    var oeLines = [];
    for (var k in oeDefaultObj) {
        oeLines.push(k + ': ' + oeDefaultObj[k]);
    }
    var ccSource = config.cc || defaults.cc || stageTitle;
    var invSource = config.inv || defaults.inv || '';
    var ccHtml = admissionPrintEscape(ccSource).replace(/\n/g, '<br>');
    var oeHtml = admissionPrintEscape(oeLines.join('\n')).replace(/\n/g, '<br>');
    var invHtml = admissionPrintEscape(invSource).replace(/\n/g, '<br>');

    return '<!DOCTYPE html>' +
        '<html><head><title>' + title + '</title>' +
        '<style>' +
        '@page { size: A4 portrait; margin: 0; }' +
        '* { box-sizing: border-box; }' +
        'body { margin: 0; background: #eef1f5; color: #061a35; font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; line-height: 1.35; }' +
        '.rx-page { width: 190mm; min-height: 277mm; margin: 0 auto; padding: 4cm 12mm 10mm; background: #fff; box-shadow: 0 10px 26px rgba(15, 23, 42, .14); }' +
        '.rx-header { display: grid; grid-template-columns: 1.2fr .8fr; gap: 16mm; align-items: start; }' +
        '.rx-brand { display: flex; gap: 10px; align-items: flex-start; }' +
        '.rx-logo { max-width: 54px; max-height: 54px; object-fit: contain; }' +
        '.rx-doctor-name { margin: 0; font-size: 20px; font-weight: 800; color: #061a35; line-height: 1.1; }' +
        '.rx-doctor-line { margin-top: 3px; font-size: 10.5px; font-weight: 700; color: #0c2f65; }' +
        '.rx-doctor-sub { margin-top: 3px; font-size: 10px; color: #284563; }' +
        '.rx-clinic { text-align: right; font-size: 9.5px; color: #273f5a; padding-top: 3px; }' +
        '.rx-clinic strong { display: block; margin-bottom: 2px; color: #061a35; font-size: 10px; }' +
        '.rx-rule { display: none !important; }' +
        '.rx-ref-line { display: none !important; }' +
        '.rx-info { display: grid; grid-template-columns: 42mm 1fr; gap: 5mm; min-height: 62mm; }' +
        '.rx-cc { border-right: 1px solid #e2e8f0; padding-right: 5mm; color: #061a35; }' +
        '.rx-cc-title { font-weight: 800; margin-bottom: 7px; }' +
        '.rx-cc-note { color: #163f73; font-size: 11.5px; line-height: 1.5; }' +
        '.rx-patient-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 18px; margin-bottom: 10px; }' +
        '.rx-field { border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; min-height: 24px; }' +
        '.rx-field b { margin-right: 5px; font-weight: 800; color: #061a35; }' +
        '.rx-mark { margin: 9px 0 8px; font-family: Georgia, "Times New Roman", serif; font-size: 25px; font-style: italic; font-weight: 800; color: #111827; }' +
        '.rx-med-table { width: 100%; border-collapse: collapse; font-size: 10.5px; color: #071b35; }' +
        '.rx-med-table th, .rx-med-table td { border: 1px solid #475569; padding: 5px 2px; text-align: center; vertical-align: middle; }' +
        '.rx-med-table th { font-weight: 700; background: #f8fafc; color: #0f172a; font-size: 8pt; letter-spacing: 0.2px; white-space: nowrap; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
        '.rx-med-table .rx-med-head { width: 30%; text-align: left; }' +
        '.rx-med-name { text-align: left !important; font-weight: 700; font-size: 10.5pt; color: #0f172a; }' +
        '.rx-med-name span { display: block; margin-top: 2px; font-size: 8.5pt; font-weight: 500; color: #64748b; }' +
        '.rx-empty-row td { height: 26px; }' +
        '.rx-signature { display: none !important; }' +
        '.rx-footer { display: none !important; }' +
        '@media print { body { background: #fff; } .rx-page { width: auto; min-height: auto; margin: 0; padding: 4cm 12mm 10mm !important; box-shadow: none; } }' +
        '</style></head><body>' +
        '<main class="rx-page">' +
        '<section class="rx-info"><aside class="rx-cc">' +
        '<div class="rx-cc-title">C/C:</div><div class="rx-cc-note">' + ccHtml + '</div>' +
        '<div class="rx-cc-title" style="margin-top:15px;border-top:1px solid #e2e8f0;padding-top:10px;">O/E:</div><div class="rx-cc-note">' + oeHtml + '</div>' +
        '<div class="rx-cc-title" style="margin-top:15px;border-top:1px solid #e2e8f0;padding-top:10px;">Inv:</div><div class="rx-cc-note">' + invHtml + '</div>' +
        '</aside><div>' +
        '<div class="rx-patient-grid">' +
        '<div class="rx-field"><b>&#2480;&#2507;&#2455;&#2496;&#2480; &#2453;&#2507;&#2465;:</b> ' + (patientCode || '&nbsp;') + '</div>' +
        '<div class="rx-field"><b>&#2480;&#2507;&#2455;&#2496;&#2480; &#2472;&#2494;&#2478;:</b> ' + (patientName || '&nbsp;') + '</div>' +
        '<div class="rx-field"><b>&#2478;&#2507;&#2476;&#2494;&#2439;&#2482;:</b> ' + (mobile || '&nbsp;') + '</div>' +
        '<div class="rx-field"><b>&#2477;&#2480;&#2509;&#2468;&#2495;&#2480; &#2468;&#2494;&#2480;&#2495;&#2454;:</b> ' + (admissionDate || today) + '</div>' +
        '</div>' +
        '<table class="rx-med-table">' +
        '<colgroup><col style="width:30%"><col style="width:8.5%"><col style="width:8.5%"><col style="width:8.5%"><col style="width:8%"><col style="width:8%"><col style="width:10.5%"><col style="width:18%"></colgroup>' +
        '<thead>' +
        '<tr><th rowspan="2" class="rx-med-head">&#2451;&#2487;&#2497;&#2471;&#2503;&#2480; &#2472;&#2494;&#2478;</th>' +
        '<th colspan="3">&#2453;&#2454;&#2472; &#2454;&#2494;&#2476;&#2503;&#2472;?</th>' +
        '<th colspan="2">&#2438;&#2489;&#2494;&#2480;&#2503;&#2480;</th>' +
        '<th rowspan="2">&#2453;&#2468;&#2470;&#2495;&#2472;</th>' +
        '<th rowspan="2">&#2472;&#2495;&#2480;&#2509;&#2470;&#2503;&#2488;&#2472;&#2494;</th></tr>' +
        '<tr><th>&#2488;&#2453;&#2494;&#2482;</th><th>&#2470;&#2497;&#2474;&#2497;&#2480;</th><th>&#2480;&#2494;&#2468;</th>' +
        '<th>&#2438;&#2455;&#2503;</th><th>&#2474;&#2480;&#2503;</th></tr>' +
        '</thead><tbody>' + rows + '</tbody></table>' +
        '</div></section>' +
        '</main></body></html>';
}
