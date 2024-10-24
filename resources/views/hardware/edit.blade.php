@extends('layouts/edit-form', [
    'createText' => trans('admin/hardware/form.create'),
    'updateText' => trans('admin/hardware/form.update'),
    // 'topSubmit' => true,
    // 'helpText' => trans('help.assets'),
    'helpPosition' => 'right',
    'formAction' => $item->id ? route('hardware.update', ['hardware' => $item->id]) : route('hardware.store'),
])


{{-- Page content --}}
@section('inputFields')

    <div class="form-group {{ $errors->has('project_id') ? ' has-error' : '' }}">
        <label for="company_id" class="col-md-3 control-label">
            {{ trans('general.asset') }} {{ trans('general.supplier') }}
        </label>
        <div class="col-md-7 col-sm-12 required">
            <select name="company_id" id="company_id" class="form-control select2 h-100" style="width: 100%; padding: 120px;"
                required>
                <option value="">Select {{ trans('general.asset') }} {{ trans('general.supplier') }}</option>
                @foreach ($company as $companies)
                    <option value="{{ $companies->id }}" data-company="{{ $companies->name }}"
                        {{ $item->id != null && $item->company_id === $companies->id ? 'selected' : '' }}>
                        {{ $companies->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 col-sm-1 text-left">
            <a href='#' class="btn btn-sm btn-primary" data-toggle="modal"
                data-target="#modal-create-asset-supplier">{{ trans('button.new') }}</a>
            <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
            </span>
        </div>
    </div>

    {{-- MODAL CREATE NEW SUPPLIER --}}

    <div class="modal" id="modal-create-asset-supplier">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Asset Supplier</h4>
                    {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button> --}}
                </div>
                <div class="modal-body">
                    <form action="#" onsubmit="return false;">
                        <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">{{ trans('general.name') }}</label>
                            <div class="col-sm-10">
                                <input type='text' name="name" id='name' class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                    <button type="button" class="btn btn-primary" id="save-asset-supplier"
                        data-company='{{ route('api.companies.store') }}'>Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Asset Tag -->
    <div class="form-group {{ $errors->has('asset_tag') ? ' has-error' : '' }}">
        <label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }}</label>



        @if ($item->id)
            <!-- we are editing an existing asset,  there will be only one asset tag -->
            <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'asset_tag') ? ' required' : '' }}">


                <input class="form-control" type="text" name="asset_tags" id="asset_tag"
                    value="{{ old('asset_tag', $item->asset_tag) }}" data-validation="required">
                {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
            </div>
        @else
            <!-- we are creating a new asset - let people use more than one asset tag -->
            <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'asset_tag') ? ' required' : '' }}">
                <input class="form-control" type="text" name="asset_tags" id="asset_tag"
                    value="{{ old('asset_tags', \App\Models\Asset::autoincrement_asset()) }}" data-validation="required">
                {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
            </div>
            <div class="col-md-2 col-sm-12">
                {{-- <button class="add_field_button btn btn-default btn-sm">
                  <i class="fas fa-plus"></i>
              </button> --}}
            </div>
        @endif
    </div>


    {{--    @include ('partials.forms.edit.serial', ['fieldname'=> 'serials[1]', 'old_val_name' => 'serials.1', 'translated_serial' => trans('admin/hardware/form.serial')]) --}}
    {{-- @include ('partials.forms.edit.name', ['translated_name' => trans('admin/hardware/form.name')]) --}}

    <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name" class="col-md-3 control-label">
            {{ trans('admin/hardware/form.name') }}
        </label>
        <div class="col-md-7 col-sm-12 required">
            @if ($item->id)
                <input type="text" class="form-control" name="asset_name" id="name"
                    value="{{ old('asset_name') ?? $item->name }}" required>
            @else
                <input type="text" class="form-control" name="asset_name" id="name" required>
            @endif
        </div>
    </div>



    <div class="input_fields_wrap">
    </div>


    @include ('partials.forms.edit.model-select', [
        'translated_name' => trans('admin/hardware/form.model'),
        'fieldname' => 'model_id',
        'field_req' => true,
    ])


    <div id='custom_fields_content'>
        <!-- Custom Fields -->
        @if ($item->model && $item->model->fieldset)
            <?php $model = $item->model; ?>
        @endif
        @if (Request::old('model_id'))
            @php
                $model = \App\Models\AssetModel::find(old('model_id'));
            @endphp
        @elseif (isset($selected_model))
            @php
                $model = $selected_model;
            @endphp
        @endif
        @if (isset($model) && $model)
            @include('models/custom_fields_form', ['model' => $model])
        @endif
    </div>


    {{-- ganti nanti ya --}}
    @include ('partials.forms.edit.status', ['required' => 'true'])


    @php
        $currency_type = null;
        if ($item->id && $item->location) {
            $currency_type = $item->location->currency;
        }
        $tipe_field = 1;
    @endphp

    @include ('partials.forms.edit.notes')
    @include ('partials.forms.edit.location-select', [
        'translated_name' => trans('admin/hardware/form.default_location'),
        'fieldname' => 'rtd_location_id',
        'required' => 'true',
    ])
    {{-- @include ('partials.forms.edit.requestable', ['requestable_text' => trans('admin/hardware/general.requestable')]) --}}
    @include ('partials.forms.edit.warranty')
    @include ('partials.forms.edit.purchase_date')
    @include ('partials.forms.edit.image-upload-multiple', ['image_path' => app('assets_upload_path')])

@stop
@push('js')
    <script nonce="{{ csrf_token() }}" src="{{ url('js/custom-modals-create-asset-model.js') }}"></script>
@endpush
@section('moar_scripts')

    <script nonce="{{ csrf_token() }}">
        @if (Request::has('model_id'))
            //TODO: Refactor custom fields to use Livewire, populate from server on page load when requested with model_id
            $(document).ready(function() {
                fetchCustomFields()
            });
        @endif
        $(document).ready(function() {

            generateAssetNumber();

            function generateAssetNumber() {
                var $assetTagInput = $('#asset_tag')
                var inputValueInit = $assetTagInput.val();
                @if (!$item->id)
                    $assetTagInput.val('20-' + inputValueInit);
                @endif


                $('#company_id').change(function() {
                    var selected = $(this).find('option:selected');
                    var extra = selected.data('company').toLowerCase().replace(/\s+/g, '_');

                    // Get the current input value
                    var inputValue = $assetTagInput.val();

                    // Set the prefix based on the selected company
                    var newPrefix = (extra === 'oceanside_solutions_llc') ? '10-' : '20-';

                    // Remove the existing prefix before adding the new one
                    var existingPrefixes = ['20-', '10-'];
                    existingPrefixes.forEach(function(prefix) {
                        if (inputValue.startsWith(prefix)) {
                            inputValue = inputValue.slice(prefix.length);
                        }
                    });

                    // Update the inputValue with the new prefix
                    $assetTagInput.val(newPrefix + inputValue);
                });
            }
        });


        var transformed_oldvals = {};


        function fetchCustomFields() {
            //save custom field choices
            var oldvals = $('#custom_fields_content').find('input,select').serializeArray();
            for (var i in oldvals) {
                transformed_oldvals[oldvals[i].name] = oldvals[i].value;
            }

            var modelid = $('#model_select_id').val();
            if (modelid == '') {
                $('#custom_fields_content').html("");
            } else {

                $.ajax({
                    type: 'GET',
                    url: "{{ config('app.url') }}/family/" + modelid + "/custom_fields",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    _token: "{{ csrf_token() }}",
                    dataType: 'html',
                    success: function(data) {
                        $('#custom_fields_content').html(data);
                        $('#custom_fields_content select')
                            .select2(); //enable select2 on any custom fields that are select-boxes
                        //now re-populate the custom fields based on the previously saved values
                        $('#custom_fields_content').find('input,select').each(function(index, elem) {
                            if (transformed_oldvals[elem.name]) {
                                {{-- If there already *is* is a previously-input 'transformed_oldvals' handy,
                                  overwrite with that previously-input value *IF* this is an edit of an existing item *OR*
                                  if there is no new default custom field value coming from the model --}}
                                if ({{ $item->id ? 'true' : 'false' }} || $(elem).val() == '') {
                                    $(elem).val(transformed_oldvals[elem.name]).trigger(
                                        'change'
                                    ); //the trigger is for select2-based objects, if we have any
                                }
                            }

                        });
                    }
                });
            }
        }


        function user_add(status_id) {

            if (status_id != '') {
                $(".status_spinner").css("display", "inline");
                $.ajax({
                    url: "{{ config('app.url') }}/api/v1/statuslabels/" + status_id + "/deployable",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        $(".status_spinner").css("display", "none");
                        $("#selected_status_status").fadeIn();

                        if (data == true) {
                            $("#assignto_selector").show();
                            $("#assigned_user").show();

                            $("#selected_status_status").removeClass('text-danger');
                            $("#selected_status_status").removeClass('text-warning');
                            $("#selected_status_status").addClass('text-success');
                            $("#selected_status_status").html(
                                '<i class="fas fa-check"></i> {{ trans('admin/hardware/form.asset_deployable') }}'
                            );


                        } else {
                            $("#assignto_selector").hide();
                            $("#selected_status_status").removeClass('text-danger');
                            $("#selected_status_status").removeClass('text-success');
                            $("#selected_status_status").addClass('text-warning');
                            $("#selected_status_status").html(
                                '<i class="fas fa-exclamation-triangle"></i> {{ trans('admin/hardware/form.asset_not_deployable') }} '
                            );
                        }
                    }
                });
            }
        }


        $(function() {
            //grab custom fields for this model whenever model changes.
            $('#model_select_id').on("change", fetchCustomFields);

            // $('#company_select').on("change", cobaFungsi);

            //initialize assigned user/loc/asset based on statuslabel's statustype
            user_add($(".status_id option:selected").val());

            //whenever statuslabel changes, update assigned user/loc/asset
            $(".status_id").on("change", function() {
                user_add($(".status_id").val());
            });

        });
    </script>
@stop
