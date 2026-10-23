@extends('adminlte::page')

@section('title', 'Edit Patient')

@section('content_header')
  <h1 class="text-primary">Edit Patient (রোগী তথ্য সংশোধন)</h1>
@stop

@section('content')

{{-- ======================= Reference Modal ======================= --}}
<div class="modal fade" id="refModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Reference Person</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="refMsg" class="small"></div>

        <div class="mb-2">
          <label class="form-label">Type</label>
          <select id="ref_type_modal" class="form-control">
            <option value="">-- Select Type --</option>
            <option value="OfficeEmployee">Office Employee</option>
            <option value="PCNurse">PC / Nurse</option>
            <option value="MidWife">Mid Wife</option>
            <option value="Others">Others</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Name</label>
          <input type="text" id="ref_name_modal" class="form-control" placeholder="Reference person name">
        </div>

        <div class="mb-2">
          <label class="form-label">Mobile</label>
          <input type="text" id="ref_mobile_modal" class="form-control" placeholder="01xxxxxxxxx">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnSaveRef">Save</button>
      </div>

    </div>
  </div>
</div>

{{-- ======================= Location Modal ======================= --}}
<div class="modal fade" id="locModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="locModalTitle">Add</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="locMsg" class="small mb-2"></div>

        <input type="hidden" id="loc_kind">
        <div class="mb-2">
          <label class="form-label">Name</label>
          <input type="text" id="loc_name" class="form-control" placeholder="Enter name">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnSaveLoc">Save</button>
      </div>

    </div>
  </div>
</div>

<div class="container mt-3">

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Whoops!</strong> Please fix the following errors:
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <form action="{{ route('patients.updatepatient', ['id' => $patient->id]) }}" method="post" id="patientForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <table class="table table-borderless align-middle w-100" style="max-width:900px;">
          <tbody>

            {{-- Photo --}}
                        <tr>
              <td class="fw-semibold" style="width:220px;">Photo</td>
              <td style="width:10px;">:</td>
              <td>
                <img id="previewImg"
                     src="{{ !empty($patient->photo) ? asset('uploads/photos/' . $patient->photo) : asset('uploads/photos/no_image.png') }}"
                     style="width:150px;height:150px;border:1px solid #ccc;object-fit:cover;margin:8px 0;">
                <br>

                <input type="file"
                       name="photo"
                       id="photoInput"
                       accept="image/*"
                       onchange="previewImage(this)">

                <button type="button" class="btn btn-info btn-sm mt-2" onclick="openCamera()">📷 Open Camera</button>

                <div id="cameraBox" style="display:none; margin-top:10px;">
                  <video id="camera" width="200" height="150" autoplay playsinline style="border:1px solid #ccc;"></video><br>

                  <button type="button" class="btn btn-success btn-sm mt-2" onclick="capturePhoto()">Capture</button>
                  <button type="button" class="btn btn-danger btn-sm mt-2" onclick="closeCamera()">Close</button>

                  <canvas id="canvas" width="200" height="150" style="display:none;"></canvas>
                </div>

                <input type="hidden" name="camera_image" id="camera_image">

                @error('photo')
                  <div style="color:red;margin-top:6px;">{{ $message }}</div>
                @enderror
              </td>
            </tr>
            {{-- Patient code --}}
            <tr>
              <td class="fw-semibold" style="width:220px;">Patient Code (রোগী কোড)</td>
              <td style="width:10px;">:</td>
              <td>
                <input type="text"
                       name="patientcode"
                       class="form-control"
                       value="{{ old('patientcode', $patient->patientcode) }}"
                       readonly>
              </td>
            </tr>

            {{-- Registration Date --}}
            <tr>
              <td class="fw-semibold">Registration Date (নিবন্ধনের তারিখ)</td>
              <td>:</td>
              <td>
                <input type="datetime-local"
                       name="registration_date"
                       class="form-control @error('registration_date') is-invalid @enderror"
                       value="{{ old('registration_date', $patient->registration_date ? \Carbon\Carbon::parse($patient->registration_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                @error('registration_date')
                  <div style="color:red;margin-top:6px;">{{ $message }}</div>
                @enderror
              </td>
            </tr>

            {{-- Name --}}
            <tr>
              <td class="fw-semibold">Patient Name (নাম) <span style="color:red; font-size:22px; font-weight:bold; line-height:1;">*</span></td>
              <td>:</td>
              <td>
                <input type="text" name="patientname" class="form-control @error('patientname') is-invalid @enderror" required value="{{ old('patientname', $patient->patientname) }}">
                @error('patientname') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Father --}}
            <tr>
              <td class="fw-semibold">Patient father Name (পিতার নাম)</td>
              <td>:</td>
              <td>
                <input type="text" name="patientfather" class="form-control @error('patientfather') is-invalid @enderror" value="{{ old('patientfather', $patient->patientfather) }}">
                @error('patientfather') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Husband --}}
            <tr>
              <td class="fw-semibold">Husband Name (স্বামীর নাম)</td>
              <td>:</td>
              <td>
                <input type="text" name="patienthusband" class="form-control @error('patienthusband') is-invalid @enderror" value="{{ old('patienthusband', $patient->patienthusband) }}">
                @error('patienthusband') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Mobiles --}}
            <tr>
              <td class="fw-semibold">Own Mobile (নিজের মোবাইল) <span style="color:red; font-size:22px; font-weight:bold; line-height:1;">*</span></td>
              <td>:</td>
              <td>
                <input type="text"
                       id="mobile_no"
                       name="mobile_no"
                       class="form-control @error('mobile_no') is-invalid @enderror"
                       value="{{ old('mobile_no', $patient->mobile_no) }}"
                       pattern="01[0-9]{9}"
                       maxlength="11"
                       minlength="11"
                       placeholder="01XXXXXXXXX"
                       required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('mobile_no')
                  <div style="color:red;margin-top:6px;">{{ $message }}</div>
                @enderror
              </td>
            </tr>

            <tr>
              <td class="fw-semibold">Spouse Mobile (স্বামী/স্ত্রীর মোবাইল)</td>
              <td>:</td>
              <td>
                <input type="text"
                       id="spomobile_no"
                       name="spomobile_no"
                       class="form-control @error('spomobile_no') is-invalid @enderror"
                       value="{{ old('spomobile_no', $patient->spomobile_no) }}"
                       pattern="01[0-9]{9}"
                       maxlength="11"
                       minlength="11"
                       placeholder="01XXXXXXXXX"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('spomobile_no') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            <tr>
              <td class="fw-semibold">Relative Mobile (আত্মীয়ের মোবাইল)</td>
              <td>:</td>
              <td>
                <input type="text"
                       id="relmobile_no"
                       name="relmobile_no"
                       class="form-control @error('relmobile_no') is-invalid @enderror"
                       value="{{ old('relmobile_no', $patient->relmobile_no) }}"
                       pattern="01[0-9]{9}"
                       maxlength="11"
                       minlength="11"
                       placeholder="01XXXXXXXXX"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('relmobile_no')
                  <div style="color:red;margin-top:6px;">{{ $message }}</div>
                @enderror
              </td>
            </tr>

            {{-- Email --}}
            <tr>
              <td class="fw-semibold">Email</td>
              <td>:</td>
              <td>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email) }}">
                @error('email') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- DOB --}}
            <tr>
              <td class="fw-semibold">Date of Birth (জন্মতারিখ)</td>
              <td>:</td>
              <td>
                <input type="date" name="date_of_birth" id="dob" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : '') }}">
                @error('date_of_birth') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Age --}}
            <tr>
              <td class="fw-semibold">Age (বয়স) <span style="color:red; font-size:22px; font-weight:bold; line-height:1;">*</span></td>
              <td>:</td>
              <td>
                <input type="text" name="age" id="age" class="form-control @error('age') is-invalid @enderror" required value="{{ old('age', $patient->age) }}">
                @error('age') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- NID --}}
            <tr>
              <td class="fw-semibold">NID Number</td>
              <td>:</td>
              <td>
                <input type="text" name="nid_number" class="form-control @error('nid_number') is-invalid @enderror" value="{{ old('nid_number', $patient->nid_number) }}">
                @error('nid_number') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Gender --}}
            <tr>
              <td class="fw-semibold">
                Gender (লিঙ্গ)
                <span class="text-danger fw-bold" style="font-size:22px; line-height:1;">*</span>
              </td>
              <td>:</td>
              <td>
                @php($g = old('gender', $patient->gender))
                <select name="gender" class="form-select select2 @error('gender') is-invalid @enderror" required>
                  <option value="">-- Select --</option>
                  <option value="Male" {{ $g === 'Male' ? 'selected' : '' }}>Male</option>
                  <option value="Female" {{ $g === 'Female' ? 'selected' : '' }}>Female</option>
                  <option value="Other" {{ $g === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Blood group --}}
            <tr>
              <td class="fw-semibold">Blood Group (রক্তের গ্রুপ)</td>
              <td>:</td>
              <td>
                <select name="blood_group" class="form-control select2 @error('blood_group') is-invalid @enderror">
                  <option value="">-- Select Blood Group --</option>
                  @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                  @endforeach
                </select>
                @error('blood_group') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- District --}}
            <tr>
              <td class="fw-semibold">District (জেলা)</td>
              <td>:</td>
              <td class="d-flex gap-2 align-items-center">
                <select id="district" name="district" class="form-control select2 @error('district') is-invalid @enderror">
                  <option value="">-- Select District --</option>
                  @foreach($districts as $d)
                    <option value="{{ $d->code }}" {{ old('district', $patient->district) == $d->code ? 'selected' : '' }}>
                      {{ $d->name }}
                    </option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnAddDistrict">+</button>
              </td>
            </tr>

            {{-- Upozila --}}
            <tr>
              <td class="fw-semibold">Upozila</td>
              <td>:</td>
              <td class="d-flex gap-2 align-items-center">
                <select id="upozila" name="upozila" class="form-control select2" disabled>
                  <option value="">-- Select Upozila --</option>
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnAddUpozila" disabled>+</button>
              </td>
            </tr>

            {{-- Union --}}
            <tr>
              <td class="fw-semibold">Union</td>
              <td>:</td>
              <td class="d-flex gap-2 align-items-center">
                <select id="union" name="union" class="form-control select2" disabled>
                  <option value="">-- Select Union --</option>
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnAddUnion" disabled>+</button>
              </td>
            </tr>

            {{-- Village --}}
            <tr>
              <td class="fw-semibold">Village</td>
              <td>:</td>
              <td class="d-flex gap-2 align-items-center">
                <select id="village" name="village" class="form-control select2" disabled>
                  <option value="">-- Select Village --</option>
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnAddVillage" disabled>+</button>
              </td>
            </tr>

            {{-- Post --}}
            <tr>
              <td class="fw-semibold">Post (পোস্ট)</td>
              <td>:</td>
              <td class="d-flex gap-2 align-items-center">
                <select id="post" name="post" class="form-control select2" disabled>
                  <option value="">-- Select Post --</option>
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnAddPost" disabled>+</button>
              </td>
            </tr>

            {{-- Reference --}}
            <tr>
              <td class="fw-semibold">Reference Type</td>
              <td>:</td>
              <td>
                @php($rt = old('reference_type', $patient->reference_type))
                <select name="reference_type" id="reference_type" class="form-control">
                  <option value="">-- Select Type --</option>
                  <option value="Self" {{ $rt==='Self' ? 'selected':'' }}>Self</option>
                  <option value="OfficeEmployee" {{ $rt==='OfficeEmployee' ? 'selected':'' }}>Office Employee</option>
                  <option value="PCNurse" {{ $rt==='PCNurse' ? 'selected':'' }}>PC / Nurse</option>
                  <option value="MidWife" {{ $rt==='MidWife' ? 'selected':'' }}>Mid Wife</option>
                  <option value="NOCOM" {{ $rt==='NOCOM' ? 'selected':'' }}>No Commission</option>
                  <option value="Others" {{ $rt==='Others' ? 'selected':'' }}>Others</option>
                </select>

                <button type="button" class="btn btn-success btn-sm" id="btnAddRef" title="Add Reference Person">+</button>
              </td>
            </tr>

            <tr id="personRow" style="display:none;">
              <td class="fw-semibold">Reference Person</td>
              <td>:</td>
              <td>
                <select name="reference_person" id="reference_person" class="form-control">
                  <option value="">-- Select Person --</option>
                </select>
              </td>
            </tr>

            <tr id="manualRow" style="display:none;">
              <td class="fw-semibold">Reference Name</td>
              <td>:</td>
              <td>
                <input type="text" name="reference_name" id="reference_name" class="form-control"
                       value="{{ old('reference_name', $patient->reference_name) }}"
                       placeholder="Enter Reference Name">
              </td>
            </tr>

            {{-- Notes --}}
            <tr>
              <td class="fw-semibold">Notes (অন্যান্য)</td>
              <td>:</td>
              <td>
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $patient->notes) }}</textarea>
                @error('notes') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

          </tbody>
        </table>

        <div class="mt-3">
          <button class="btn btn-primary">Update</button>
          <a href="{{ route('patients.searchpatient') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>
@stop

@push('js')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  const OLD_DISTRICT = @json(old('district', $patient->district));
  const OLD_UPOZILA  = @json(old('upozila', $patient->upozila));
  const OLD_UNION    = @json(old('union', $patient->union));
  const OLD_VILLAGE  = @json(old('village', $patient->village));
  const OLD_POST     = @json(old('post', $patient->post));

  const OLD_REF_TYPE   = @json(old('reference_type', $patient->reference_type));
  const OLD_REF_PERSON = @json(old('reference_person', $patient->reference_person));
</script>

<script>
$(function () {
  const CSRF = "{{ csrf_token() }}";

  $(".select2").select2();

  function resetUpozila(){
    $("#upozila").prop('disabled', true).html('<option value="">-- Select Upozila --</option>').trigger('change.select2');
    $("#btnAddUpozila").prop('disabled', true);
    resetUnion();
  }
  function resetUnion(){
    $("#union").prop('disabled', true).html('<option value="">-- Select Union --</option>').trigger('change.select2');
    $("#btnAddUnion").prop('disabled', true);
    resetVillage();
  }
  function resetVillage(){
    $("#village").prop('disabled', true).html('<option value="">-- Select Village --</option>').trigger('change.select2');
    $("#btnAddVillage").prop('disabled', true);
    resetPost();
  }
  function resetPost(){
    $("#post").prop('disabled', true).html('<option value="">-- Select Post --</option>').trigger('change.select2');
    $("#btnAddPost").prop('disabled', true);
  }

  function loadUpozila(district, selected, cb){
    resetUpozila();
    if(!district) return;

    $("#upozila").html('<option value="">Loading...</option>').prop('disabled', true);

    $.post("{{ route('api.fetch_upozila') }}", { _token: CSRF, district: district }, function(html){
      $("#upozila").html(html).prop('disabled', false).trigger('change.select2');
      $("#btnAddUpozila").prop('disabled', false);

      if(selected) $("#upozila").val(String(selected)).trigger('change');
      else $("#upozila").val("").trigger('change');

      if(typeof cb === 'function') cb();
    }).fail(function(){
      $("#upozila").html('<option value="">Failed to load</option>').prop('disabled', true);
    });
  }

  function loadUnion(upozila, selected, cb){
    resetUnion();
    if(!upozila) return;

    $("#union").html('<option value="">Loading...</option>').prop('disabled', true);

    $.post("{{ route('api.fetch_union') }}", { _token: CSRF, upozila: upozila }, function(html){
      $("#union").html(html).prop('disabled', false).trigger('change.select2');
      $("#btnAddUnion").prop('disabled', false);

      if(selected) $("#union").val(String(selected)).trigger('change');
      else $("#union").val("").trigger('change');

      if(typeof cb === 'function') cb();
    }).fail(function(){
      $("#union").html('<option value="">Failed to load</option>').prop('disabled', true);
    });
  }

  function loadVillage(union, selected, cb){
    resetVillage();
    if(!union) return;

    $("#village").html('<option value="">Loading...</option>').prop('disabled', true);

    $.post("{{ route('api.fetch_village') }}", { _token: CSRF, union: union }, function(html){
      $("#village").html(html).prop('disabled', false).trigger('change.select2');
      $("#btnAddVillage").prop('disabled', false);

      if(selected) $("#village").val(String(selected)).trigger('change');
      else $("#village").val("").trigger('change');

      if(typeof cb === 'function') cb();
    }).fail(function(){
      $("#village").html('<option value="">Failed to load</option>').prop('disabled', true);
    });
  }

  function loadPost(village, selected){
    resetPost();
    if(!village) return;

    $("#post").html('<option value="">Loading...</option>').prop('disabled', true);

    $.post("{{ route('api.fetch_post') }}", { _token: CSRF, village: village }, function(html){
      $("#post").html(html).prop('disabled', false).trigger('change.select2');
      $("#btnAddPost").prop('disabled', false);

      if(selected) $("#post").val(String(selected)).trigger('change');
      else $("#post").val("").trigger('change');
    }).fail(function(){
      $("#post").html('<option value="">Failed to load</option>').prop('disabled', true);
    });
  }

  $("#district").on("change", function(){
    loadUpozila(this.value, null);
  });
  $("#upozila").on("change", function(){
    loadUnion(this.value, null);
  });
  $("#union").on("change", function(){
    loadVillage(this.value, null);
  });
  $("#village").on("change", function(){
    loadPost(this.value, null);
  });

  if (OLD_DISTRICT) {
    loadUpozila(OLD_DISTRICT, OLD_UPOZILA, function(){
      loadUnion(OLD_UPOZILA, OLD_UNION, function(){
        loadVillage(OLD_UNION, OLD_VILLAGE, function(){
          loadPost(OLD_VILLAGE, OLD_POST);
        });
      });
    });
  } else {
    resetUpozila();
  }

  function showRefUI(type){
    $("#personRow").hide();
    $("#manualRow").hide();

    if(!type) return;

    if(type === "Self" || type === "Others"){
      $("#manualRow").show();
      return;
    }
    $("#personRow").show();
  }

  function loadReferencePersons(type, selectedId = ''){
    $("#reference_person").html('<option value="">-- Select Person --</option>');
    if(!type || type === 'Self' || type === 'Others' || type === 'NOCOM') return;

    $("#reference_person").html('<option value="">Loading...</option>');
    $.post("{{ route('api.fetch_reference_person') }}", { _token: CSRF, reference_type: type }, function(html){
      $("#reference_person").html(html);
      if(selectedId) $("#reference_person").val(String(selectedId));
    }).fail(function(){
      $("#reference_person").html('<option value="">Failed to load</option>');
    });
  }

  $("#reference_type").on("change", function(){
    const type = this.value;
    showRefUI(type);
    loadReferencePersons(type);
  });

  if (OLD_REF_TYPE) {
    $("#reference_type").val(OLD_REF_TYPE);
    showRefUI(OLD_REF_TYPE);
    loadReferencePersons(OLD_REF_TYPE, OLD_REF_PERSON);
  }

  $("#btnAddRef").on("click", function(){
    const currentType = $("#reference_type").val();

    if (currentType && currentType !== 'Self') $("#ref_type_modal").val(currentType);
    else $("#ref_type_modal").val('');

    $("#ref_name_modal").val('');
    $("#ref_mobile_modal").val('');
    $("#refMsg").html('');
    $("#refModal").modal('show');
  });

  $("#btnSaveRef").on("click", function(){
    const ref_type = $("#ref_type_modal").val();
    const name     = $("#ref_name_modal").val();
    const mobile   = $("#ref_mobile_modal").val();

    $("#refMsg").html('');

    $.ajax({
      type: "POST",
      url: "{{ route('api.reference_person.store') }}",
      dataType: "json",
      data: { _token: CSRF, ref_type: ref_type, Name: name, Mobile: mobile },
      success: function(res){
        if (!res.ok) {
          $("#refMsg").html('<div class="text-danger">Failed to save.</div>');
          return;
        }

        $("#reference_type").val(res.type).trigger('change');
        loadReferencePersons(res.type, res.id);

        $("#refModal").modal('hide');
      },
      error: function(){
        $("#refMsg").html('<div class="text-danger">Validation failed / server error.</div>');
      }
    });
  });

  function openLocModal(kind){
    $("#loc_kind").val(kind);
    $("#loc_name").val('');
    $("#locMsg").html('');

    let title = 'Add';
    if(kind==='district') title = 'Add District';
    if(kind==='upozila')  title = 'Add Upozila';
    if(kind==='union')    title = 'Add Union';
    if(kind==='village')  title = 'Add Village';
    if(kind==='post')     title = 'Add Post';

    $("#locModalTitle").text(title);
    $("#locModal").modal('show');
  }

  $("#btnAddDistrict").click(()=> openLocModal('district'));
  $("#btnAddUpozila").click(()=> openLocModal('upozila'));
  $("#btnAddUnion").click(()=> openLocModal('union'));
  $("#btnAddVillage").click(()=> openLocModal('village'));
  $("#btnAddPost").click(()=> openLocModal('post'));

  $("#district").on("change", function(){
    $("#btnAddUpozila").prop('disabled', !this.value);
    $("#btnAddUnion").prop('disabled', true);
    $("#btnAddVillage").prop('disabled', true);
    $("#btnAddPost").prop('disabled', true);
  });
  $("#upozila").on("change", function(){
    $("#btnAddUnion").prop('disabled', !this.value);
    $("#btnAddVillage").prop('disabled', true);
    $("#btnAddPost").prop('disabled', true);
  });
  $("#union").on("change", function(){
    $("#btnAddVillage").prop('disabled', !this.value);
    $("#btnAddPost").prop('disabled', true);
  });
  $("#village").on("change", function(){
    $("#btnAddPost").prop('disabled', !this.value);
  });

  function reloadSelect(kind, parentIds, selectedId){
    if(kind === 'district'){
      $.post("{{ route('api.fetch_district') }}", { _token: CSRF }, function(html){
        $("#district").html(html).trigger('change.select2');
        $("#district").val(String(selectedId)).trigger('change');
      });
      return;
    }

    if(kind === 'upozila'){
      loadUpozila(parentIds.district, selectedId);
      return;
    }
    if(kind === 'union'){
      loadUnion(parentIds.upozila, selectedId);
      return;
    }
    if(kind === 'village'){
      loadVillage(parentIds.union, selectedId);
      return;
    }
    if(kind === 'post'){
      loadPost(parentIds.village, selectedId);
      return;
    }
  }

  $("#btnSaveLoc").on("click", function(){
    const kind = $("#loc_kind").val();
    const name = ($("#loc_name").val() || '').trim();
    if(!name){
      $("#locMsg").html('<div class="text-danger">Name is required.</div>');
      return;
    }

    const district = $("#district").val();
    const upozila  = $("#upozila").val();
    const union    = $("#union").val();
    const village  = $("#village").val();

    let storeUrl = '';
    let payload  = { _token: CSRF, name: name };

    if(kind === 'district'){
      storeUrl = "{{ route('api.district.store') }}";
    }
    if(kind === 'upozila'){
      if(!district){ $("#locMsg").html('<div class="text-danger">Select District first.</div>'); return; }
      storeUrl = "{{ route('api.upozila.store') }}";
      payload.district = district;
    }
    if(kind === 'union'){
      if(!upozila){ $("#locMsg").html('<div class="text-danger">Select Upozila first.</div>'); return; }
      storeUrl = "{{ route('api.union.store') }}";
      payload.upozila = upozila;
    }
    if(kind === 'village'){
      if(!union){ $("#locMsg").html('<div class="text-danger">Select Union first.</div>'); return; }
      storeUrl = "{{ route('api.village.store') }}";
      payload.union = union;
    }
    if(kind === 'post'){
      if(!village){ $("#locMsg").html('<div class="text-danger">Select Village first.</div>'); return; }
      storeUrl = "{{ route('api.post.store') }}";
      payload.village = village;
    }

    $.ajax({
      type: "POST",
      url: storeUrl,
      dataType: "json",
      data: payload,
      success: function(res){
        if(!res.ok){
          $("#locMsg").html('<div class="text-danger">'+(res.message || 'Failed')+'</div>');
          return;
        }

        reloadSelect(kind, { district, upozila, union, village }, res.id);
        $("#locModal").modal('hide');
      },
      error: function(){
        $("#locMsg").html('<div class="text-danger">Server error / validation failed.</div>');
      }
    });
  });

});
</script>

<script>
  $(function(){
    $("#dob").on("change", function () {
      const dob = $(this).val();
      if (!dob) return;

      const birthDate = new Date(dob);
      const today = new Date();

      let years  = today.getFullYear() - birthDate.getFullYear();
      let months = today.getMonth() - birthDate.getMonth();
      let days   = today.getDate() - birthDate.getDate();

      if (days < 0) {
        const prevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
        days += prevMonth.getDate();
        months--;
      }
      if (months < 0) {
        months += 12;
        years--;
      }

      if (years < 0 || years > 150) {
        $("#age").val('');
        return;
      }

      $("#age").val(years + " Years " + months + " Months " + days + " Days");
    });
  });
</script>

<script>
  function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file.');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => document.getElementById('previewImg').src = e.target.result;
    reader.readAsDataURL(file);
  }
</script>

<script>
  document.getElementById('patientForm').addEventListener('submit', function (e) {
    const fields = [
      { id: 'mobile_no',     name: 'Mobile No', required: true },
      { id: 'spomobile_no',  name: 'Spouse Mobile No', required: false },
      { id: 'relmobile_no',  name: 'Relative Mobile No', required: false }
    ];

    for (let i = 0; i < fields.length; i++) {
      const el = document.getElementById(fields[i].id);
      if (!el) continue;

      const val = el.value.trim();

      if (val === '' && !fields[i].required) continue;

      if (val !== '' && !/^01[0-9]{9}$/.test(val)) {
        alert(fields[i].name + ' must start with 01 and be exactly 11 digits');
        el.focus();
        e.preventDefault();
        return false;
      }
    }
  });
</script>

<script>
$(function () {
    $('.select2').select2();

    $('#dob').on('change', function () {
        const dob = $(this).val();
        if (!dob) return;

        const birthDate = new Date(dob);
        const today = new Date();

        let years  = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        let days   = today.getDate() - birthDate.getDate();

        if (days < 0) {
            const prevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            days += prevMonth.getDate();
            months--;
        }

        if (months < 0) {
            months += 12;
            years--;
        }

        if (years < 0 || years > 150) {
            $('#age').val('');
            return;
        }

        $('#age').val(years + ' Years ' + months + ' Months ' + days + ' Days');
    });
});

function previewImage(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('previewImg').src = e.target.result;

        // manual file selected হলে camera image clear
        document.getElementById('camera_image').value = '';
    };
    reader.readAsDataURL(file);
}

let stream = null;

function openCamera() {
    document.getElementById('cameraBox').style.display = 'block';

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (s) {
            stream = s;
            document.getElementById('camera').srcObject = stream;
        })
        .catch(function () {
            alert('Camera access denied or not available.');
        });
}

function capturePhoto() {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/png');

    document.getElementById('previewImg').src = imageData;
    document.getElementById('camera_image').value = imageData;

    // camera capture হলে file input clear
    document.getElementById('photoInput').value = '';

    closeCamera();
}

function closeCamera() {
    document.getElementById('cameraBox').style.display = 'none';

    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

document.getElementById('patientForm').addEventListener('submit', function (e) {
    const fields = [
        { id: 'mobile_no', name: 'Mobile No', required: true },
        { id: 'spomobile_no', name: 'Spouse Mobile No', required: false },
        { id: 'relmobile_no', name: 'Relative Mobile No', required: false }
    ];

    for (let i = 0; i < fields.length; i++) {
        const el = document.getElementById(fields[i].id);
        if (!el) continue;

        const val = el.value.trim();

        if (val === '' && !fields[i].required) continue;

        if (val !== '' && !/^01[0-9]{9}$/.test(val)) {
            alert(fields[i].name + ' must start with 01 and be exactly 11 digits');
            el.focus();
            e.preventDefault();
            return false;
        }
    }
});
</script>
@endpush