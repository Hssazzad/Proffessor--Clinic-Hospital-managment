<option value="">Select GL Sub Group</option>@foreach($groups as $group)<option value="{{ $group->gl_sub_group }}">{{ $group->gl_sub_group_name }}</option>@endforeach
