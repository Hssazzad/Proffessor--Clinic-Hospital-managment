@php
    $pid       = $pid ?? request('id');
    $patientId = $patientId ?? request('patient');
    $wz        = $wz ?? 'rx';
@endphp

<form method="post" action="{{ route($wz.'.patient.save') }}" id="patientSelectForm">
    @csrf
    <input type="hidden" name="prescription_id" value="{{ $pid }}">
    <input type="hidden" name="patient_id" id="patient_id" value="{{ $patientId }}">

    {{-- store selected previous rx (if any) --}}
    <input type="hidden" name="previous_prescription_id" id="previous_prescription_id" value="">

    <div class="mb-2">
        <label class="form-label fw-bold">Search Patient</label>
        <input type="text" id="patientFilter" class="form-control"
               placeholder="Type to filter by name / mobile / code">
        <small class="text-muted">
            All patients preloaded. Filter is client-side.
        </small>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Select Patient *</label>
        <select id="patientSelect" class="form-control" size="5" required>
            @foreach ($patients as $p)
                <option value="{{ $p->id }}"
                        data-name="{{ $p->patientname ?? '' }}"
                        data-mobile="{{ $p->mobile_no ?? '' }}"
                        data-code="{{ $p->patientcode ?? '' }}"
                        @selected((string)$patientId === (string)$p->id)>
                    {{ $p->patientname }} — {{ $p->mobile_no }} ({{ $p->patientcode }})
                </option>
            @endforeach
        </select>
    </div>

    <div id="patientInfo" class="bg-light p-2 rounded mb-3 border" style="display:none"></div>

    <div class="text-end">
        <input type="hidden" name="next" value="complain">
        <button class="btn btn-primary" type="submit">
            Next → Complain
        </button>
    </div>
</form>

<script>
(function(){
    const $ = (id) => document.getElementById(id);
    const select   = $('patientSelect');
    const info     = $('patientInfo');
    const hiddenId = $('patient_id');
    const filter   = $('patientFilter');
    const prevHidden = $('previous_prescription_id');

    const prevUrlTemplate   = "{{ route($wz.'.patient.prev', 0) }}";   // /ces/patient/0/previous-prescriptions
    const detailsUrlTemplate= "{{ route($wz.'.prev.details', 0) }}";   // /ces/prescription/0/details

    function baseInfoHtml(opt){
        return `
            <div>
                ✅ <b>${opt.dataset.name}</b>
                📞 ${opt.dataset.mobile}
                🏷️ Code: ${opt.dataset.code}
            </div>
            <div id="prevRxWrap" class="mt-2">
                <div class="text-muted small">Loading previous prescriptions...</div>
            </div>
            <div id="prevRxDetails" class="mt-2"></div>
        `;
    }

    function renderPrevDropdown(list){
        if(!list || !list.length){
            return `<div class="text-muted small">No previous prescriptions found.</div>`;
        }

        let options = `<option value="">-- Select Previous Prescription --</option>`;
        list.forEach(rx => {
            let dt = '';
            if (rx.created_at) {
                try { dt = new Date(rx.created_at).toLocaleDateString(); } catch(e){}
            }
            options += `<option value="${rx.id}">RX #${rx.id}${dt ? ' — ' + dt : ''}</option>`;
        });

        return `
            <label class="form-label fw-bold mt-1">Previous Prescriptions</label>
            <select id="prevRxSelect" class="form-control form-control-sm">
                ${options}
            </select>
            <small class="text-muted">Select any previous RX to view details.</small>
        `;
    }

    async function loadPreviousPrescriptions(patientId){
        const wrap = document.getElementById('prevRxWrap');
        const detailsBox = document.getElementById('prevRxDetails');
        if (!wrap) return;

        wrap.innerHTML = `<div class="text-muted small">Loading previous prescriptions...</div>`;
        detailsBox.innerHTML = '';
        prevHidden.value = '';

        try{
            const url = prevUrlTemplate.replace('/0', '/' + patientId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();

            if(!json.ok){
                wrap.innerHTML = `<div class="text-danger small">Failed to load previous prescriptions.</div>`;
                return;
            }

            wrap.innerHTML = renderPrevDropdown(json.data);

            const prevSel = document.getElementById('prevRxSelect');
            prevSel?.addEventListener('change', function(){
                const rxId = this.value;
                prevHidden.value = rxId || '';
                detailsBox.innerHTML = '';
                if(rxId){
                    loadPreviousDetails(rxId);
                }
            });

        }catch(e){
            wrap.innerHTML = `<div class="text-danger small">Error loading previous prescriptions.</div>`;
        }
    }

    async function loadPreviousDetails(rxId){
        const detailsBox = document.getElementById('prevRxDetails');
        detailsBox.innerHTML = `<div class="text-muted small">Loading previous prescription details...</div>`;

        try{
            const url = detailsUrlTemplate.replace('/0', '/' + rxId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();

            if(!json.ok){
                detailsBox.innerHTML = `<div class="text-danger small">Failed to load details.</div>`;
                return;
            }

            const d = json.data;

            detailsBox.innerHTML = `
                <div class="border-top pt-2 mt-2">
                    <div class="fw-bold mb-1">Previous RX Summary (RX #${rxId})</div>

                    <div class="small">
                        <b>Complaints:</b> ${d.complains.length}<br>
                        <b>Diagnosis:</b> ${d.diagnosis.length}<br>
                        <b>Investigations:</b> ${d.investigations.length}<br>
                        <b>Medicines:</b> ${d.medicines.length}
                    </div>

                    <div class="mt-2 small">
                        ${d.complains.length ? '<b>Complaints:</b><ul class="mb-1">' + d.complains.map(c=>`<li>${c.complaint}${c.note ? ' — '+c.note : ''}</li>`).join('') + '</ul>' : ''}
                        ${d.diagnosis.length ? '<b>Diagnosis:</b><ul class="mb-1">' + d.diagnosis.map(x=>`<li>${x.name}${x.note ? ' — '+x.note : ''}</li>`).join('') + '</ul>' : ''}
                        ${d.investigations.length ? '<b>Investigations:</b><ul class="mb-1">' + d.investigations.map(i=>`<li>${i.name}${i.note ? ' — '+i.note : ''}</li>`).join('') + '</ul>' : ''}
                    </div>

                    <div class="mt-2">
                        <a class="btn btn-xs btn-outline-primary"
                           href="{{ route($wz.'.wizard') }}?tab=preview&id=${rxId}&patient=${hiddenId.value}">
                           View Previous RX
                        </a>
                    </div>
                </div>
            `;

        }catch(e){
            detailsBox.innerHTML = `<div class="text-danger small">Error loading details.</div>`;
        }
    }

    function updateInfo() {
        const opt = select.options[select.selectedIndex];
        if (!opt) {
            info.style.display = 'none';
            hiddenId.value = '';
            return;
        }

        hiddenId.value = opt.value;
        info.innerHTML = baseInfoHtml(opt);
        info.style.display = 'block';

        loadPreviousPrescriptions(opt.value);
    }

    select.addEventListener('change', updateInfo);

    filter.addEventListener('input', function(){
        const q = this.value.toLowerCase().trim();
        for (const opt of select.options) {
            const text = (opt.textContent + opt.dataset.name + opt.dataset.mobile + opt.dataset.code).toLowerCase();
            opt.hidden = q && !text.includes(q);
        }
        const visibleOpts = Array.from(select.options).filter(o => !o.hidden);
        if (visibleOpts.length) {
            select.value = visibleOpts[0].value;
            updateInfo();
        } else {
            hiddenId.value = '';
            info.style.display = 'none';
        }
    });

    updateInfo();
})();
</script>

<script>
(function(){
  const form = document.getElementById('patientSelectForm');
  form.addEventListener('submit', function(e){
    const pid = document.getElementById('patient_id').value;
    if (!pid) {
      e.preventDefault();
      alert('Please select a patient first.');
    }
  });
})();
</script>