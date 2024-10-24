<!-- Name -->
<div class="form-group {{ $errors->has('project_number') ? ' has-error' : '' }}">
    <label for="project_number" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'project_number') ? ' required' : '' }}">
        <input class="form-control" type="number" name="project_number" aria-label="project_number" id="project_number"
            value="{{ old('project_number', $item->project_number) }}"{!! Helper::checkIfRequired($item, 'project_number') ? ' data-validation="required" required' : '' !!} />
        {!! $errors->first(
            'project_number',
            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
        ) !!}
    </div>
</div>
