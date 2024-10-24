@extends('layouts.edit-form', [
    'createText' => 'Create ' . trans('general.company_movement'),
    'updateText' => 'Update ' . trans('general.company_movement'),
    'formAction' => $item->id ? route('company-movement.update', $item->id) : route('company-movement.store'),
])

@section('inputFields')
    <div class="form-group {{ $errors->has('company_name') ? ' has-error' : '' }}">
        <label for="company_name" class="col-md-3 control-label">{{ trans('general.company') }} Name</label>
        <div class="col-md-7 col-sm-12 required">
            <input class="form-control" type="text" name="company_name" aria-label="company_name" id="company_name"
                value="{{ $item->id ? $item->company_name : old('company_name') }}" required maxlength="100"
                placeholder="Company Branch" />
            {!! $errors->first(
                'company_name',
                '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
            ) !!}
        </div>
    </div>
    <div class="form-group {{ $errors->has('address') ? ' has-error' : '' }}">
        <label for="address" class="col-md-3 control-label">{{ trans('admin/movement/form.address') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <textarea class="form-control" type="text" name="address" aria-label="address" id="address" required>{{ $item->id ? $item->address : old('address') }}</textarea>
            {!! $errors->first(
                'address',
                '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
            ) !!}
        </div>
    </div>
@endsection



@section('moar_scripts')
@endsection
