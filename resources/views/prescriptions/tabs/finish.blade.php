@php
  $pid       = (int)($pid ?? request('id'));
  $patientId = (int)($patient->id ?? request('patient'));
  $wz        = $wz ?? 'rx';
@endphp

<style>
  .card{background:#fff;border:1px solid #e5eaf3;border-radius:.75rem;padding:1rem;margin-bottom:1rem}
  .btn{padding:.5rem .9rem;border-radius:.5rem;border:1px solid #dbe3f0;background:#0ea5e9;color:#fff;cursor:pointer}
  .btn.secondary{background:#f8fafc;color:#0e1a2a}
  .btn.danger{background:#ef4444}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
</style>

<div class="card">
  <h3 style="margin-top:0">Finish</h3>
  <p style="color:#475569">You can print/download the current prescription, or start a new one.</p>

  <div class="grid2">
  
    <div>
     
      <div class="card" style="margin:0">
        <form method="post" action="{{ route($wz.'.finish.new') }}" style="display:flex;flex-direction:column;gap:.5rem">
          @csrf
         
		     <input type="hidden" name="patient_id" value="{{ $patientId ?: '' }}">    
          
        
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <button class="btn" type="submit">Finish & Start New Prescription</button>
           
         </div>
        </form>
      
    </div>
  </div>
</div>


