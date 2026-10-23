<option value="">Select GL Group</option>@foreach($groups as $group)<option value="{{ $group->gl_group }}">{{ $group->gl_group_name }}</option>@endforeach
