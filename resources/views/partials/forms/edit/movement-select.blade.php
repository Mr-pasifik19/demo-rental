<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!! isset($style) ? ' style="' . e($style) . '"' : '' !!}>

    {{ Form::label($fieldname, $translated_name, ['class' => 'col-md-3 control-label']) }}
    <div class="col-md-7 {{ isset($required) && $required == 'true' ? 'required' : '' }}">
        <select name="{{ $fieldname }}" id="{{ $fieldname }}" class="form-contro select2" style="width: 100%;">
            <option value="">Select Movement</option>
            @foreach (\App\Models\MovementsModel::with(['project', 'status'])->whereHas('status', function ($query) {
            $query->where('name', 'Available')->orWhere('name', 'In Project');
        })->latest()->get() as $movement)
                <option value="{{ $movement->id }}">
                    {{ $movement->status->name . ' - ' . $movement->project->project_name }}
                </option>
            @endforeach

        </select>
        {!! $errors->first(
            $fieldname,
            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
        ) !!}
    </div>
</div>
