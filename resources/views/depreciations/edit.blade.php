@extends('layouts/edit-form', [
    'createText' => trans('admin/depreciations/general.create') ,
    'updateText' => trans('admin/depreciations/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.depreciations'),
    'formAction' => (isset($item->id)) ? route('depreciations.update', ['depreciation' => $item->id]) : route('depreciations.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/depreciations/general.depreciation_name')])
<!-- Months -->
<div class="form-group {{ $errors->has('months') ? ' has-error' : '' }}">
    <label for="months" class="col-md-3 control-label">
        {{ trans('admin/depreciations/general.number_of_months') }}
    </label>
    <div class="col-md-7 col-sm-12 {{  (Helper::checkIfRequired($item, 'months')) ? ' required' : '' }}">
        <div class="col-md-8" style="padding-left:0px; width: 104%">
            <input class="form-control" type="text" name="months" id="months" value="{{ Request::old('months', $item->months) }}" {!!  (\App\Helpers\Helper::checkIfRequired($item, 'months')) ? ' data-validation="required" required' : '' !!} 
            style=""/>
            {!! $errors->first('months', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>

<!-- Depreciation Minimum -->
<div class="form-group {{ $errors->has('depreciation_min') ? ' has-error' : '' }}">
    <label for="depreciation_min" class="col-md-3 control-label">
        {{ trans('admin/depreciations/general.depreciation_min') }}
    </label>
    <div class="input-group col-md-7 col-sm-11"   style="padding-left: 0; text-align: left;">
        <input class="form-control" name="depreciation_min" id="depreciation_min" value="{{ Request::old('depreciation_min', $item->depreciation_min) }}" style="width: 96%; margin-left: 15px;" />
        <span class="input-group-addon control-label" style="padding: 5px; width:15px;">USD</span>

        
    </div>
    {!! $errors->first('depreciation_min', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
</div>

@stop
