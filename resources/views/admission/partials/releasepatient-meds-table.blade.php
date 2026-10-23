@if($rows->isEmpty())
  <tr>
    <td colspan="3" class="text-center text-muted">No medicines added.</td>
  </tr>
@else
  @foreach($rows as $r)
    <tr>
      <td>{{ $r->medicine_name ?? '-' }}</td>
      <td>{{ $r->instruction ?? '-' }}</td>
      <td class="text-center">
        <button type="button"
                class="btn btn-danger btn-sm btn-delete-med"
                data-id="{{ $r->id }}">
          &times;
        </button>
      </td>
    </tr>
  @endforeach
@endif
