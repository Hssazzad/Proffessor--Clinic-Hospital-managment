@extends('adminlte::page')

@section('title', 'Add Patient')

@section('content_header')
  <h1 class="text-primary">Add Patient (রোগী নিবন্ধন)</h1>
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
      <form action="{{ route('patients.store') }}" method="post" id="patientForm" enctype="multipart/form-data">
        @csrf

        <table class="table table-borderless align-middle w-100" style="max-width:900px;">
          <tbody>

            {{-- Photo --}}
            <tr>
              <td class="fw-semibold" style="width:220px;">Photo</td>
              <td style="width:10px;">:</td>
              <td>
                <img id="previewImg"
                     src="{{ asset('uploads/photos/no_image.png') }}"
                     style="width:150px;height:150px;border:1px solid #ccc;object-fit:cover;margin:8px 0;">
                <br>

                <input type="file"
                       name="photo"
                       id="photoInput"
                       accept="image/*"
                       onchange="previewImage(this)">

                <button type="button"
                        class="btn btn-info btn-sm mt-2"
                        onclick="openCamera()">📷 Camera</button>

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
                       value="{{ old('patientcode', $newCode ?? '') }}"
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
                       value="{{ old('registration_date', now()->format('Y-m-d\TH:i')) }}">
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
                <input type="text" name="patientname" class="form-control @error('patientname') is-invalid @enderror" required value="{{ old('patientname') }}">
                @error('patientname') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Father --}}
            <tr>
              <td class="fw-semibold">Patient father Name (পিতার নাম)</td>
              <td>:</td>
              <td>
                <input type="text" name="patientfather" class="form-control @error('patientfather') is-invalid @enderror" value="{{ old('patientfather') }}">
                @error('patientfather') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Spouse --}}
            <tr>
              <td class="fw-semibold">Spouse Name (স্বামী/স্ত্রীর নাম)</td>
              <td>:</td>
              <td>
                <input type="text" name="patienthusband" class="form-control @error('patienthusband') is-invalid @enderror" value="{{ old('patienthusband') }}">
                @error('patienthusband') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Own Mobile --}}
            <tr>
              <td class="fw-semibold">Own Mobile (নিজের মোবাইল) <span style="color:red; font-size:22px; font-weight:bold; line-height:1;">*</span></td>
              <td>:</td>
              <td>
                <input type="text"
                       id="mobile_no"
                       name="mobile_no"
                       class="form-control @error('mobile_no') is-invalid @enderror"
                       value="{{ old('mobile_no') }}"
                       pattern="01[0-9]{9}"
                       placeholder="01XXXXXXXXX"
                       required
                       oninput="validateMobile(this)">
                <div id="mobileMsg" style="color:red;margin-top:6px;display:none;">Invalid phone number</div>
                @error('mobile_no')
                  <div style="color:red;margin-top:6px;">{{ $message }}</div>
                @enderror
              </td>
            </tr>

            {{-- Spouse Mobile --}}
            <tr>
              <td class="fw-semibold">Spouse Mobile (স্বামী/স্ত্রীর মোবাইল)</td>
              <td>:</td>
              <td>
                <input type="text"
                       id="spomobile_no"
                       name="spomobile_no"
                       class="form-control @error('spomobile_no') is-invalid @enderror"
                       value="{{ old('spomobile_no') }}"
                       pattern="01[0-9]{9}"
                       maxlength="11"
                       minlength="11"
                       placeholder="01XXXXXXXXX"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('spomobile_no') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- Relative Mobile --}}
            <tr>
              <td class="fw-semibold">Relative Mobile (আত্মীয়ের মোবাইল)</td>
              <td>:</td>
              <td>
                <input type="text"
                       id="relmobile_no"
                       name="relmobile_no"
                       class="form-control @error('relmobile_no') is-invalid @enderror"
                       value="{{ old('relmobile_no') }}"
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
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- DOB --}}
           <tr>
			  <td class="fw-semibold">Date of Birth (জন্মতারিখ)</td>
			  <td>:</td>
			  <td>
				<input type="text"
				   name="date_of_birth"
				   id="dob"
				   class="form-control @error('date_of_birth') is-invalid @enderror"
				   value="{{ old('date_of_birth') }}"
				   placeholder="dd-mm-yyyy"
				   maxlength="10"
				   oninput="formatDOB(this)">

				@error('date_of_birth')
				  <div style="color:red;margin-top:6px;">{{ $message }}</div>
				@enderror
			  </td>
			</tr>

            {{-- Age --}}
            <tr>
              <td class="fw-semibold">Age (বয়স) <span style="color:red; font-size:22px; font-weight:bold; line-height:1;">*</span></td>
              <td>:</td>
              <td>
                <input type="text" name="age" id="age" class="form-control @error('age') is-invalid @enderror" required value="{{ old('age') }}" oninput="validateAge(this)">
                <div id="ageMsg" style="color:red;margin-top:6px;display:none;">Invalid number</div>
                @error('age') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

            {{-- NID --}}
            <tr>
              <td class="fw-semibold">NID Number</td>
              <td>:</td>
              <td>
                <input type="text" name="nid_number" class="form-control @error('nid_number') is-invalid @enderror" value="{{ old('nid_number') }}">
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
                @php($g = old('gender'))
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
                    <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
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
                    <option value="{{ $d->code }}" {{ old('district') == $d->code || ($d->code === 'Bogura' && !old('district')) ? 'selected' : '' }}>
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
              <td class="fw-semibold">Post Office (Optional)</td>
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
                @php($rt = old('reference_type'))
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
                       value="{{ old('reference_name') }}"
                       placeholder="Enter Reference Name">
              </td>
            </tr>

            {{-- Notes --}}
            <tr>
              <td class="fw-semibold">Notes (অন্যান্য)</td>
              <td>:</td>
              <td>
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes') <div style="color:red;margin-top:6px;">{{ $message }}</div> @enderror
              </td>
            </tr>

          </tbody>
        </table>

        <div class="mt-3">
          <button class="btn btn-primary">Save</button>
          <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
  const OLD_DISTRICT = @json(old('district'));
  const OLD_UPOZILA  = @json(old('upozila'));
  const OLD_UNION    = @json(old('union'));
  const OLD_VILLAGE  = @json(old('village'));
  const OLD_POST     = @json(old('post'));

  const OLD_REF_TYPE   = @json(old('reference_type'));
  const OLD_REF_PERSON = @json(old('reference_person'));
</script>

<script>
$(function () {
  const CSRF = "{{ csrf_token() }}";

  $(".select2").select2();

  validateMobile(document.getElementById('mobile_no'));
  validateAge(document.getElementById('age'));

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

  var initDistrict = OLD_DISTRICT || $("#district").val();
  var initUpozila  = OLD_UPOZILA || (initDistrict === 'Bogura' ? 'Shajahanpur' : null);
  var initUnion    = OLD_UNION   || (initUpozila === 'Shajahanpur' ? 'Majhira' : null);
  if (initDistrict) {
    loadUpozila(initDistrict, initUpozila, function(){
      loadUnion(initUpozila, initUnion, function(){
        loadVillage(initUnion, OLD_VILLAGE, function(){
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
    if(!type || type === 'Self' || type === 'Others') return;

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

 $("#dob").on("change", function () {
    const dob = $(this).val().trim();
    if (!dob) {
        $("#age").val("");
        return;
    }

    // expected format: dd-mm-yyyy
    const parts = dob.split("-");
    if (parts.length !== 3) {
        $("#age").val("");
        return;
    }

    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);

    if (isNaN(day) || isNaN(month) || isNaN(year)) {
        $("#age").val("");
        return;
    }

    const birthDate = new Date(year, month - 1, day);

    // invalid date check
    if (
        birthDate.getFullYear() !== year ||
        birthDate.getMonth() !== month - 1 ||
        birthDate.getDate() !== day
    ) {
        $("#age").val("");
        return;
    }

    const today = new Date();

    let years = today.getFullYear() - year;
    let months = today.getMonth() - (month - 1);
    let days = today.getDate() - day;

    if (days < 0) {
        months--;
        const prevMonthDays = new Date(today.getFullYear(), today.getMonth(), 0).getDate();
        days += prevMonthDays;
    }

    if (months < 0) {
        years--;
        months += 12;
    }

    if (years < 0 || years > 120) {
        $("#age").val("");
        return;
    }

    // if same day selected, show 0 year 0 month 1 day
    if (years === 0 && months === 0 && days === 0) {
        days = "less than 1";
    }

    $("#age").val(years + " Years " + months + " Months " + days + " Days");
});

});
</script>

<script>
let stream = null;

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
        document.getElementById('camera_image').value = '';
    };
    reader.readAsDataURL(file);
}

function openCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Camera API not supported in this browser.');
        return;
    }

    document.getElementById('cameraBox').style.display = 'block';

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (s) {
            stream = s;
            document.getElementById('camera').srcObject = stream;
        })
        .catch(function (err) {
            console.log(err);
            alert('Camera not allowed or not supported');
        });
}

function capturePhoto() {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/png');

    document.getElementById('previewImg').src = imageData;
    document.getElementById('camera_image').value = imageData;
    document.getElementById('photoInput').value = '';

    console.log('camera_image set:', imageData.substring(0, 50));

    closeCamera();
}

function closeCamera() {
    document.getElementById('cameraBox').style.display = 'none';

    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

function validateMobile(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    var msg = document.getElementById('mobileMsg');
    if (input.value.length > 11) {
        msg.style.display = 'block';
        input.setCustomValidity('Invalid phone number');
    } else {
        msg.style.display = 'none';
        input.setCustomValidity('');
    }
}

function validateAge(input) {
    var num = parseInt(input.value, 10);
    var msg = document.getElementById('ageMsg');
    if (!isNaN(num) && num > 120) {
        msg.style.display = 'block';
        input.setCustomValidity('Invalid number');
    } else {
        msg.style.display = 'none';
        input.setCustomValidity('');
    }
}

document.getElementById('patientForm').addEventListener('submit', function (e) {
    validateMobile(document.getElementById('mobile_no'));
    validateAge(document.getElementById('age'));

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

      if (val.length > 11 || (val !== '' && !/^01[0-9]{9}$/.test(val))) {
        alert(fields[i].name + ' — Invalid phone number');
        el.focus();
        e.preventDefault();
        return false;
      }
    }

    const ageEl = document.getElementById('age');
    const ageNum = parseInt(ageEl.value, 10);
    if (!isNaN(ageNum) && ageNum > 120) {
        alert('Age — Invalid number');
        ageEl.focus();
        e.preventDefault();
        return false;
    }
});
</script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function () {
    $("#dob").datepicker({
        dateFormat: "dd-mm-yy",   // Laravel format
        changeMonth: true,        // month dropdown
        changeYear: true,         // year dropdown
        yearRange: "1950:+0",     // from 1900 to current year
        maxDate: 0                // disable future date
    });
});
</script>

<script>
function formatDOB(input) {
    let value = input.value.replace(/\D/g, ''); // only numbers

    if (value.length > 8) value = value.substring(0, 8);

    let formatted = '';

    if (value.length > 0) {
        formatted = value.substring(0, 2);
    }
    if (value.length >= 3) {
        formatted += '-' + value.substring(2, 4);
    }
    if (value.length >= 5) {
        formatted += '-' + value.substring(4, 8);
    }

    input.value = formatted;
}
</script>
@endpush
