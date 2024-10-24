@extends('layouts/edit-form', [
    'createText' => trans('admin/movement/form.create'),
    'updateText' => trans('admin/movement/form.update'),
    'helpPosition' => 'right',
    'topSubmit' => true,
    'formAction' => $item->id ? route('movement.update', ['movement' => $item->id]) : route('movement.saveMovement'),
])

@push('css')
    <link href=" https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css " rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

{{-- Page content --}}
@section('inputFields')
    <input type="hidden" name="is_update" id="idUpdate" value="store">
    <input type="hidden" name="idMovement" id="idMovement" value="">
    {{-- projectSelect --}}
    <div class="form-group {{ $errors->has('project_id') ? ' has-error' : '' }}">
        <label for="project_id" class="col-md-3 control-label">{{ trans('admin/movement/form.project') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <select name="project_id" id="project_id" class="form-control select2 h-100"
                style="width: 100%; padding: 120px;" required>
                <option value="">Select Project</option>
                @foreach ($projectMovement as $project)
                    <option value="{{ $project->id }}">{{ $project->project_number . ' - ' . $project->project_name }}
                    </option>
                @endforeach
            </select>

        </div>
    </div>
    <!-- Asset Tag -->
    <div class="form-group {{ $errors->has('movement_tag') ? ' has-error' : '' }}">
        <label for="movement_tag" class="col-md-3 control-label">{{ trans('admin/movement/form.tag') }}</label>

        @if ($item->id)
            <!-- Editing an existing asset, only one asset tag -->
            <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'movement_tag') ? ' required' : '' }}">
                <input class="form-control" type="text" name="movement_numbers" id="movement_number"
                    value="{{ old('movement_number', $item->movement_number) }}" data-validation="required" readonly>
                {!! $errors->first('movement_numbers', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
            </div>
        @else
            <!-- Creating a new asset - allow multiple asset tags -->
            <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'movement_number') ? ' required' : '' }}">
                <input class="form-control" type="text" name="movement_numbers" id="movement_number"
                    value="{{ old('movement_numbers') ?? $movementNumber }}" data-validation="required" readonly>
                {!! $errors->first('movement_numbers', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
            </div>
        @endif
    </div>

    <div class="input_fields_wrap">
    </div>
    {{-- Dibawah ini lokasi movement --}}
    @include ('partials.forms.edit.location-select', [
        'translated_name' => trans('admin/movement/form.locationMovement'),
        'fieldname' => 'project_location',
        'field_req' => 'true',
    ])
    <div class="form-group {{ $errors->has('company_id') ? ' has-error' : '' }}">
        <label for="company_id" class="col-md-3 control-label">{{ trans('general.select_branch') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <select name="company_id" id="company_id" class="form-control select2 h-100"
                style="width: 100%; padding: 120px;" required>
                <option value="">Select Branch</option>
                @foreach ($company as $companies)
                    <option value="{{ $companies->id . '+' . $companies->address }}">{{ $companies->company_name }}
                    </option>
                @endforeach
            </select>
            <div class="text-green mt-4" id="addressCompany">

            </div>
        </div>
    </div>

    <div class="form-group {{ $errors->has('to_receiver') ? ' has-error' : '' }}">
        <label for="to_receiver" class="col-md-3 control-label">{{ trans('admin/movement/form.toMovement') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <select name="to_receiver" id="to_receiver" class="form-control select2 h-100"
                style="width: 100%; padding: 120px;" required>
                <option value="">Select Receiver</option>
                <option value="manual">Entry Manually</option>
            </select>

        </div>
    </div>
    <div id="manualReceiver"></div>


    <div class="form-group {{ $errors->has('person_id') ? ' has-error' : '' }}">
        <label for="person_id" class="col-md-3 control-label">{{ trans('admin/movement/form.person') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <select name="person_id" id="person_id" class="form-control select2 h-100" style="width: 100%; padding: 120px;"
                required>
                <option value="">Select Person In Charge</option>
                <option value="manual">Entry Manually</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->first_name . ' ' . $user->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 col-sm-1 text-left">
            <a href='#' data-toggle="modal" data-target="#modal-create-pic"
                class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
        </div>
    </div>
    {{-- MODAL PERSON IN CHARGE --}}

    <div class="modal" id="modal-create-pic">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Person In Charge</h4>
                    {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button> --}}
                </div>
                <div class="modal-body">
                    <form action="#" onsubmit="return false;">
                        <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">{{ trans('general.first_name') }}</label>
                            <div class="col-sm-10">
                                <input type='text' name="first_name" id='first_name' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">{{ trans('general.last_name') }}</label>
                            <div class="col-sm-10">
                                <input type='text' name="last_name" id='last_name' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fphone" class="control-label col-sm-2">Phone</label>
                            <div class="col-sm-10">
                                <input type='text' name="phone" id='phone' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">Company</label>
                            <div class="col-sm-10">
                                <select name="company_id_pic" class="form-control select2" id="company_id_pic">
                                    @foreach (\App\Models\Company::all() as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                    <button type="button" class="btn btn-primary" id="save-pic">Save</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END --}}

    <div id="personCharge"></div>
    <div id="phonePersonCharge"></div>
    <div class="form-group">
        <label for="contact_recipient" class="col-md-3 control-label">Contact Recipient</label>
        <div class="col-md-7 col-sm-12 required">
            <select name="contact_recipient" id="contact_recipient" class="form-control select2 h-100"
                style="width: 100%; padding: 120px;" required>
                <option value="">Select Contact Recipient</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->first_name . ' ' . $user->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 col-sm-1 text-left">
            <a href='#' data-toggle="modal" data-target="#modal-create-cp"
                class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
        </div>
    </div>
    {{-- MODAL contact recipient --}}

    <div class="modal" id="modal-create-cp">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Contact Recipient</h4>
                    {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button> --}}
                </div>
                <div class="modal-body">
                    <form action="#" onsubmit="return false;">
                        <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        </div>
                        <div class="form-group row">
                            <label for="fname"
                                class="control-label col-sm-2">{{ trans('general.first_name') }}</label>
                            <div class="col-sm-10">
                                <input type='text' name="first_name" id='first_name' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">{{ trans('general.last_name') }}</label>
                            <div class="col-sm-10">
                                <input type='text' name="last_name" id='last_name' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fphone" class="control-label col-sm-2">Phone</label>
                            <div class="col-sm-10">
                                <input type='text' name="phone" id='phone' class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fname" class="control-label col-sm-2">Company</label>
                            <div class="col-sm-10">
                                <select name="company_id_cp" class="form-control select2" id="company_id_cp">
                                    @foreach (\App\Models\Company::all() as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                    <button type="button" class="btn btn-primary" id="save-pic">Save</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END --}}
    @include('partials.forms.edit.status-select', [
        'fieldname' => 'status_movement',
        'required' => 'true',
        'translated_name' => trans('general.statusMovement'),
    ])

    @include ('partials.forms.edit.asset-select', [
        'translated_name' => trans('general.assets'),
        'fieldname' => 'selected_assets[]',
        'multiple' => true,
        'asset_status_type' => 'RTD',
        'select_id' => 'assigned_assets_select',
        'field_req' => 'true',
    ])

    @include ('partials.forms.edit.notes')
    <input type="hidden" name="status" id="status">

    {{-- <div id="formatDate"></div> --}}
@endsection
@section('outside-box')
    <div class="modal fade" id="modal-invoice" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-static">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Print Invoice</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('movement.saveMovement') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="project_id" id="project_id_modal">
                    <input type="hidden" name="movement_numbers" id="movement_number_modal">
                    <input type="hidden" name="project_location" id="project_location_modal">
                    <input type="hidden" name="company_id" id="company_id_modal">
                    <input type="hidden" name="to_receiver" id="to_receiver_modal">
                    <input type="hidden" name="person_id" id="person_id_modal">
                    <input type="hidden" name="contact_recipient" id="contact_recipient_modal">
                    <input type="hidden" name="status_movement" id="status_movement_modal">
                    <input type="hidden" name="selected_assets" id="selected_assets_modal">
                    <input type="hidden" name="notes" id="notes_modal">
                    <input type="hidden" name="status" id="status_modal">
                    <input type="hidden" name="receiver_manual" id="receiver_manual_modal">
                    <input type="hidden" name="phonePersonInCharge" id="phonePersonInCharge_modal">
                    <input type="hidden" name="person_charge_manual" id="person_charge_manual_modal">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label for="execution_type" class="col-md-3 col-form-label"><strong>Choose Date
                                    :</strong></label>
                            <div class="col-md-9">
                                <select class="form-control" id="execution_type" aria-label="Date Selection"
                                    name="choose_date">
                                    <option value="current_date_time">Use Current Date</option>
                                    <option value="specific_date">Specific Date</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3" id="specific_date_input" style="display:none;">
                            <label for="specific_date_text" class="col-md-3 col-form-label">Specific Date & Time:</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control flatpickr" name="specific_date_text"
                                    id="specific_date_text" placeholder="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3">
                            <label for="format_date" class="col-md-3 col-form-label">Format Date
                                <code>*Optional</code></label>
                            <div class="col-md-7 col-sm-12">
                                <select name="format_date" id="format_date" class="form-control select2 h-100"
                                    style="width: 100%;">
                                    <option value="d-M-y">Default (d-M-Y)</option>
                                    {{-- <option value="manual">Entry Manually</option> --}}
                                    @foreach (\App\Models\FormatDate::all() as $formatDates)
                                        <option value="{{ $formatDates->format }}">{{ $formatDates->format }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" name="createNewFormat" class="btn btn-sm btn-primary"
                                    data-toggle="modal"
                                    data-target="#modal-formatDate">{{ trans('button.new') }}</button>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-7">
                                <label for="" class="col-form-label">Format Sample:</label><br>
                                <code>d: Day in two digits (01 to 31)</code><br>
                                <code>D: Short day name (Sun to Sat)</code><br>
                                <code>l (lowercase L): Full text day (Sunday to Saturday)</code><br>
                                <code>m: Month in two digits (01 to 12)</code><br>
                                <code>M: Short month name (Jan)</code><br>
                                <code>F: Full text month (December)</code><br>
                                <code>Y: Four-digit year (2023)</code><br>
                                <code>y: Two-digit year (23)</code>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-secondary" id="close-modal-print"
                                    aria-label="Close">Close</button>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary">Print</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="modal-formatDate">
        <div class="modal-dialog modal-lg modal-static">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Format Date</h4>
                    {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button> --}}
                </div>
                <div class="modal-body">
                    <form action="#" onsubmit="return false;">
                        <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        </div>
                        <div class="form-group row">
                            <label for="nformat" class="control-label col-sm-2">Format</label>
                            <div class="col-sm-10">
                                <input type='text' name="nformat" id='nformat' class="form-control"
                                    placeholder="Format date (Ex: D-M-Y)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="nformat" class="control-label col-sm-2">Format
                                Sample</label>
                            <div class="col-sm-10">
                                <code>
                                    d: Day in two digits (01 to 31)
                                </code><br>
                                <code>
                                    D: Short day name (Sun to Sat)
                                </code><br>
                                <code>
                                    l (lowercase L): Full text day (Sunday to Saturday)
                                </code><br>
                                <code>
                                    m: Month in two digits (01 to 12)
                                </code><br>
                                <code>
                                    M: Short month name (Jan)
                                </code><br>
                                <code>
                                    F: Full text month (December)
                                </code><br>
                                <code>
                                    Y: Four-digit year (2023)
                                </code><br>
                                <code>
                                    y: Two-digit year (23)
                                </code>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer">
                    <a href="#" id="close-format-modal" class="btn btn-default">Close</a>
                    <button type="button" class="btn btn-primary" id="save-format">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js "></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-timezone-with-data.min.js') }}"></script>
@endpush

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.flatpickr', {
                dateFormat: "d-m-Y",
                defaultDate: "today",
            });
        });
        $(document).ready(function() {
            $('#close-format-modal').click(function() {
                $('#modal-formatDate').modal('hide');
            });

            $('#close-modal-print').click(function() {
                $('#project_id_modal').val('');
                $('#movement_number_modal').val('');
                $('#project_location_modal').val('');
                $('#company_id_modal').val('');
                $('#to_receiver_modal').val('');
                $('#person_id_modal').val('');
                $('#contact_recipient_modal').val('');
                $('#status_movement_modal').val('');
                $('#selected_assets_modal').val('');
                $('#notes_modal').val('');
                $('#status_modal').val('');
                $('#receiver_manual_modal').val('');
                $('#phonePersonInCharge_modal').val('');
                $('#person_charge_manual_modal').val('')
                $('#modal-invoice').modal('hide');
            });

            $('#execution_type').on('change', function() {
                var specificDateInput = $('#specific_date_input');
                var specificDateField = $('#specific_date_text');

                if (this.value === 'specific_date') {
                    specificDateInput.show();

                } else {
                    specificDateInput.hide();
                    specificDateField.val(getCurrentDateTime());
                }
            });

            function getCurrentDateTime() {
                var now = new Date();
                var formattedDate = moment(now).tz('America/New_York').format(
                    'DD-MM-YYYY'
                );

                return formattedDate;
            }
        });

        $(function() {
            $('#modal-formatDate').on('show.bs.modal', function() {
                var modal = $(this);
                var formatDate = modal.find('.modal-body #nformat');
                modal.find('#save-format').off('click');

                modal.find('#save-format').click(function() {
                    if (formatDate.val() === "") {
                        alert('Fill this form!');
                    } else {
                        $.ajax({
                            type: 'GET',
                            dataType: 'JSON',
                            url: '{{ route('movement.saveFormatDate') }}',
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                            },
                            data: {
                                format: formatDate.val()
                            },
                            success: function(response) {
                                var value = response.payload;
                                formatDate.val("");
                                modal.modal("hide");

                                //
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.success(response.messages);
                                //
                                var formatSelect = $('#format_date');
                                formatSelect.append('<option value="' + value +
                                    '" selected="selected">' + value + '</option>');
                                formatDate.trigger('change');

                            },
                            error: function(response) {
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.error(response.messages);
                                //
                                formatDate.val("");
                                modal.modal("hide");
                            }
                        });
                    }
                });
            });

            $("#modal-create-pic").on("show.bs.modal", function() {
                let modal = $(this);
                // Use modal.find instead of $(this).find
                let firstName = modal.find(".modal-body #first_name");
                let lastName = modal.find(".modal-body #last_name");
                let phone = modal.find(".modal-body #phone");
                let companyId = modal.find(".modal-body #company_id_pic");
                let url = '{{ route('api.users.storeUserForMovement') }}';
                // Unbind previous click event handlers
                modal.find("#save-pic").off("click");
                modal.find("#save-pic").on("click", function() {
                    if (firstName.val() === "" || lastName.val() == "" || phone.val() == "" ||
                        companyId.val() == "" || companyId.val() === undefined) {
                        alert("Fill all form!");
                    } else {
                        $.ajax({
                            type: "POST",
                            url: url,
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                            },
                            data: {
                                first_name: firstName.val(),
                                last_name: lastName.val(),
                                phone: phone.val(),
                                company_id: companyId.val(),
                            },
                            success: function(t) {
                                if ("error" == t.status) {
                                    var i = "";
                                    for (var r in t.messages)
                                        i +=
                                        "<li>Problem(s) with field <i><strong>" +
                                        r +
                                        "</strong></i>: " +
                                        t.messages[r];
                                    return $("#modal_error_msg").html(i).show(), !1;
                                }
                                var o = t.payload.id,
                                    s =
                                    t.payload.name ||
                                    t.payload.first_name + " " + t.payload.last_name;
                                if (!o || !s)
                                    return (
                                        console.error(
                                            "Could not find resulting name or ID from modal-create. Name: " +
                                            s +
                                            ", id: " +
                                            o
                                        ),
                                        !1
                                    );
                                firstName.val();
                                lastName.val();
                                phone.val();
                                companyId.val();
                                modal.modal("hide");
                                var modelSelect = $("#person_id");
                                modelSelect.append(
                                    '<option value="' + o +
                                    '" selected="selected">' + s + "</option>"
                                );
                                modelSelect.trigger(
                                    "change"
                                ); // If using Select2, trigger the change event
                                // Optionally, you can also hide the error message
                                $("#modal_error_msg").hide();
                            },
                            error: function(t) {
                                (msg = t.responseJSON.messages || t.responseJSON.error),
                                $("#modal_error_msg")
                                    .html("Server Error: " + msg)
                                    .show();
                            },
                        });
                    }
                });
            });
            $("#modal-create-cp").on("show.bs.modal", function() {
                let modal = $(this);
                // Use modal.find instead of $(this).find
                let firstName = modal.find(".modal-body #first_name");
                let lastName = modal.find(".modal-body #last_name");
                let phone = modal.find(".modal-body #phone");
                let companyId = modal.find(".modal-body #company_id_cp");
                let url = '{{ route('api.users.storeUserForMovement') }}';
                // Unbind previous click event handlers
                modal.find("#save-pic").off("click");
                modal.find("#save-pic").on("click", function() {
                    if (firstName.val() === "" || lastName.val() == "" || phone.val() == "" ||
                        companyId.val() == "" || companyId.val() === undefined) {
                        alert("Fill all form!");
                    } else {
                        $.ajax({
                            type: "POST",
                            url: url,
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                            },
                            data: {
                                first_name: firstName.val(),
                                last_name: lastName.val(),
                                phone: phone.val(),
                                company_id: companyId.val(),
                            },
                            success: function(t) {
                                if ("error" == t.status) {
                                    var i = "";
                                    for (var r in t.messages)
                                        i +=
                                        "<li>Problem(s) with field <i><strong>" +
                                        r +
                                        "</strong></i>: " +
                                        t.messages[r];
                                    return $("#modal_error_msg").html(i).show(), !1;
                                }
                                var o = t.payload.id,
                                    s =
                                    t.payload.name ||
                                    t.payload.first_name + " " + t.payload.last_name;
                                if (!o || !s)
                                    return (
                                        console.error(
                                            "Could not find resulting name or ID from modal-create. Name: " +
                                            s +
                                            ", id: " +
                                            o
                                        ),
                                        !1
                                    );
                                firstName.val();
                                lastName.val();
                                phone.val();
                                companyId.val();
                                modal.modal("hide");
                                var modelSelect = $("#contact_recipient");
                                modelSelect.append(
                                    '<option value="' + o +
                                    '" selected="selected">' + s + "</option>"
                                );
                                modelSelect.trigger(
                                    "change"
                                ); // If using Select2, trigger the change event
                                // Optionally, you can also hide the error message
                                $("#modal_error_msg").hide();
                            },
                            error: function(t) {
                                (msg = t.responseJSON.messages || t.responseJSON.error),
                                $("#modal_error_msg")
                                    .html("Server Error: " + msg)
                                    .show();
                            },
                        });
                    }
                });
            });
        });
    </script>
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function() {
            var scannedData = ""; // Store the scanned QR code
            var isDataFound = false; // Indicates whether data has been found

            // Listen for focus events on the asset-select
            $('#assigned_assets_select').on('focus', function() {
                // Clear the scanned value when the input gets focus
                scannedData = "";
            });

            // Listen for keypress events (assuming the barcode scanner sends keystrokes)
            $(document).keypress(function(e) {
                if ($('#assigned_assets_select').is(':focus')) {
                    // Only execute the following code if the asset-select is in focus

                    if (e.which === 13) {
                        // Enter key pressed, indicating the end of the scanned input

                        if (isDataFound) {
                            // Data is already found, display a message
                            toastr.options.closeButton = true;
                            toastr.options.closeMethod = 'fadeOut';
                            toastr.options.closeDuration = 100;
                            toastr.warning('Asset Already Added');
                        } else {
                            // Data not found yet, continue processing
                            var scanValue = scannedData;

                            // Check if the scanned value exists in the dropdown
                            if ($('#assigned_assets_select').find("option[value='" + scanValue + "']")
                                .length > 0) {
                                // Automatically select the item
                                $('#assigned_assets_select').val(scanValue).trigger('change.select2');
                                isDataFound = true;
                            } else {
                                // Data not found, display an alert
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.error('Asset not found');
                            }
                        }

                        // Clear the scanned value for the next scan
                        scannedData = "";
                    } else {
                        // Append the scanned character to scannedData
                        scannedData += String.fromCharCode(e.which);
                    }
                }
            });
        });

        $(document).ready(function() {

            getAddressByProject();
            addNewFormAddressManual();
            personChargeManual();
            getAddressCompany();
            // formatDateManual();
            var baseUrl = "{{ url('/') }}";
            var redirectUrl = '/movement';
            $("#print-button").click(function(e) {
                e.preventDefault(); // Prevent form submission if validation fails

                $('#status').val('print');
                // Submit the form
                // $("#myForm").submit();
                let errors = [];

                // Define an array of field variables
                let fields = [{
                        selector: '#status',
                        name: 'Status'
                    },
                    {
                        selector: '#project_id',
                        name: 'Project ID'
                    },
                    {
                        selector: '#movement_number',
                        name: 'Movement Number'
                    },
                    {
                        selector: '#project_location_location_select',
                        name: 'Project Location'
                    },
                    {
                        selector: '#company_id',
                        name: 'Company ID'
                    },
                    {
                        selector: '#to_receiver',
                        name: 'To Receiver'
                    },
                    {
                        selector: '#person_id',
                        name: 'Person ID'
                    },
                    {
                        selector: '#contact_recipient',
                        name: 'Contact Recipient'
                    },
                    {
                        selector: '#status_select_id',
                        name: 'Status Movement'
                    },
                    {
                        selector: '#assigned_assets_select',
                        name: 'Selected Asset'
                    }

                ];

                // Check each field for its presence and emptiness
                fields.forEach(function(field) {
                    let value = $(field.selector).val();
                    if (typeof value === 'string' && value.trim() === '') {
                        errors.push(field.name + ' is required.');
                    }
                });

                // Check receiver_manual if it exists
                let recevier_manual = $('#recevier_manual').val();
                if ($('#recevier_manual').length > 0 && typeof recevier_manual === 'string' &&
                    recevier_manual.trim() === '') {
                    errors.push('Receiver Manual is required.');
                }

                // Check person_in_charge_manual if it exists
                let person_in_charge_manual = $('#person_charge_manual').val();
                if ($('#person_charge_manual').length > 0 && typeof person_in_charge_manual === 'string' &&
                    person_in_charge_manual.trim() === '') {
                    errors.push('Person in Charge Manual is required.');
                }

                // Check phone_person_in_charge if it exists
                let phone_person_in_charge = $('#phonePersonInCharge').val();
                if ($('#phonePersonInCharge').length > 0 && typeof phone_person_in_charge === 'string' &&
                    phone_person_in_charge.trim() === '') {
                    errors.push('Phone Person in Charge is required.');
                }

                // Display validation errors if any
                if (errors.length > 0) {
                    // Output validation errors or perform any other action like preventing form submission
                    Swal.fire({
                        title: "Opps",
                        text: "Please fill all form!",
                        icon: "warning",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                    });
                } else {

                    //
                    //show modal
                    $('#modal-invoice').modal('show');

                }
            });
            $('#modal-invoice').on('show.bs.modal', function() {
                var modal = $(this);
                let status = $('#status').val();
                let projectId = $('#project_id').val();
                let movementNumber = $('#movement_number').val();
                let projectLocation = $('#project_location_location_select').val();
                let companyId = $('#company_id').val();
                let toReceiver = $('#to_receiver').val();
                let personId = $('#person_id').val();
                let contactRecipient = $('#contact_recipient').val();
                let statusMovement = $('#status_select_id').val();
                let selectedAsset = $('#assigned_assets_select').val();
                let notes = $('#notes').val();
                let recevier_manual = $('#recevier_manual').val();
                if (recevier_manual === undefined) {
                    recevier_manual = ''; // Set to empty string if the element is not found
                }
                let personInchargeManual = $('#person_charge_manual').val();
                if (personInchargeManual === undefined) {
                    personInchargeManual = '';
                }
                let phonePersonInCharge = $('#phonePersonInCharge').val();
                if (phonePersonInCharge === undefined) {
                    phonePersonInCharge = '';
                }

                $('#project_id_modal').val(projectId);
                $('#movement_number_modal').val(movementNumber);
                $('#project_location_modal').val(projectLocation);
                $('#company_id_modal').val(companyId);
                $('#to_receiver_modal').val(toReceiver);
                $('#person_id_modal').val(personId);
                $('#contact_recipient_modal').val(contactRecipient);
                $('#status_movement_modal').val(statusMovement);
                $('#selected_assets_modal').val(selectedAsset);
                $('#notes_modal').val(notes);
                $('#status_modal').val(status);
                $('#receiver_manual_modal').val(recevier_manual);
                $('#phonePersonInCharge_modal').val(phonePersonInCharge);
                $('#person_charge_manual_modal').val(personInchargeManual)
                console.log('Project ID:', $('#project_id_modal').val());
                console.log('Movement Number:', $('#movement_number_modal').val());
                console.log('Project Location:', $('#project_location_modal').val());
                console.log('Company ID:', $('#company_id_modal').val());
                console.log('To Receiver:', $('#to_receiver_modal').val());
                console.log('Person ID:', $('#person_id_modal').val());
                console.log('Contact Recipient:', $('#contact_recipient_modal').val());
                console.log('Status Movement:', $('#status_movement_modal').val());
                console.log('Selected Assets:', $('#selected_assets_modal').val());
                console.log('Notes:', $('#notes_modal').val());
                console.log('Status:', $('#status_modal').val());
                console.log('Receiver Manual:', $('#receiver_manual_modal').val());
                console.log('Phone Person In Charge:', $('#phonePersonInCharge_modal').val());
                console.log('Person Charge Manual:', $('#person_charge_manual_modal').val());


            });
            $("#save-button").click(function(e) {
                e.preventDefault(); // Prevent form submission if validation fails

                $('#status').val('save');
                $('#create-form').removeAttr('target');
                $(this).attr('type', 'button');

                // Validation for required fields
                let errors = [];

                // Define an array of field variables
                let fields = [{
                        selector: '#status',
                        name: 'Status'
                    },
                    {
                        selector: '#project_id',
                        name: 'Project ID'
                    },
                    {
                        selector: '#movement_number',
                        name: 'Movement Number'
                    },
                    {
                        selector: '#project_location_location_select',
                        name: 'Project Location'
                    },
                    {
                        selector: '#company_id',
                        name: 'Company ID'
                    },
                    {
                        selector: '#to_receiver',
                        name: 'To Receiver'
                    },
                    {
                        selector: '#person_id',
                        name: 'Person ID'
                    },
                    {
                        selector: '#contact_recipient',
                        name: 'Contact Recipient'
                    },
                    {
                        selector: '#status_select_id',
                        name: 'Status Movement'
                    },
                    {
                        selector: '#assigned_assets_select',
                        name: 'Selected Asset'
                    },

                ];

                // Check each field for its presence and emptiness
                fields.forEach(function(field) {
                    let value = $(field.selector).val();
                    if (typeof value === 'string' && value.trim() === '') {
                        errors.push(field.name + ' is required.');
                    }
                });

                // Check receiver_manual if it exists
                let recevier_manual = $('#recevier_manual').val();
                if ($('#recevier_manual').length > 0 && typeof recevier_manual === 'string' &&
                    recevier_manual.trim() === '') {
                    errors.push('Receiver Manual is required.');
                }

                // Check person_in_charge_manual if it exists
                let person_in_charge_manual = $('#person_charge_manual').val();
                if ($('#person_charge_manual').length > 0 && typeof person_in_charge_manual === 'string' &&
                    person_in_charge_manual.trim() === '') {
                    errors.push('Person in Charge Manual is required.');
                }

                // Check phone_person_in_charge if it exists
                let phone_person_in_charge = $('#phonePersonInCharge').val();
                if ($('#phonePersonInCharge').length > 0 && typeof phone_person_in_charge === 'string' &&
                    phone_person_in_charge.trim() === '') {
                    errors.push('Phone Person in Charge is required.');
                }

                // Display validation errors if any
                if (errors.length > 0) {
                    // Output validation errors or perform any other action like preventing form submission
                    Swal.fire({
                        title: "Opps",
                        text: "Please fill all form!",
                        icon: "warning",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                    });
                } else {

                    let status = $('#status').val();
                    let projectId = $('#project_id').val();
                    let movementNumber = $('#movement_number').val();
                    let projectLocation = $('#project_location_location_select').val();
                    let companyId = $('#company_id').val();
                    let toReceiver = $('#to_receiver').val();
                    let personId = $('#person_id').val();
                    let contactRecipient = $('#contact_recipient').val();
                    let statusMovement = $('#status_select_id').val();
                    let selectedAsset = $('#assigned_assets_select').val();
                    let notes = $('#notes').val();
                    let recevier_manual = $('#recevier_manual').val();
                    if (recevier_manual === undefined) {
                        recevier_manual = ''; // Set to empty string if the element is not found
                    }
                    let personInchargeManual = $('#person_charge_manual').val();
                    if (personInchargeManual === undefined) {
                        personInchargeManual = '';
                    }
                    let phonePersonInCharge = $('#phonePersonInCharge').val();
                    if (phonePersonInCharge === undefined) {
                        phonePersonInCharge = '';
                    }
                    let isUpdate = $('#idUpdate');
                    let idMovement = $('#idMovement')

                    if (isUpdate.val() === 'store') {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('movement.saveMovement') }}',
                            data: {
                                _token: "{{ csrf_token() }}",
                                status: status,
                                company_id: companyId,
                                movement_numbers: movementNumber,
                                status_movement: statusMovement,
                                to_receiver: toReceiver,
                                person_id: personId,
                                project_id: projectId,
                                person_charge_manual: personInchargeManual,
                                contact_recipient: contactRecipient,
                                phonePersonInCharge: phonePersonInCharge,
                                receiver_manual: recevier_manual,
                                notes: notes,
                                project_location: projectLocation,
                                selected_assets: selectedAsset,
                                isUpdate: isUpdate.val(),
                                idMovement: idMovement.val()
                            },
                            success: function(success) {
                                // Handle success response
                                $('#save-button').attr('type', 'submit');
                                $('#save-button').text('Update');
                                isUpdate.val('update');
                                idMovement.val(success.payload.id);
                                Swal.fire({
                                    title: "Successfully!",
                                    text: "New movement success created",
                                    icon: "success",
                                    showCancelButton: true,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                    showDenyButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    denyButtonColor: '#4fe90c',
                                    cancelButtonText: "Leave This Page",
                                    confirmButtonText: "Stay In Here",
                                    denyButtonText: 'Make it new again'
                                }).then((result) => {
                                    if (result.isDismissed) {
                                        window.location.href = baseUrl + redirectUrl +
                                            '?status=openMovement';
                                    } else if (result.isDenied) {
                                        location.reload();
                                    } else {
                                        console.log(isUpdate.val());
                                        console.log(idMovement.val());
                                    }
                                });

                            },
                            error: function(error) {
                                Swal.fire({
                                    title: "Failed",
                                    text: error.messages,
                                    icon: "error"
                                });
                            },
                        });
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('movement.updateMovement') }}',
                            data: {
                                _token: "{{ csrf_token() }}",
                                status: status,
                                company_id: companyId,
                                movement_numbers: movementNumber,
                                status_movement: statusMovement,
                                to_receiver: toReceiver,
                                person_id: personId,
                                project_id: projectId,
                                person_charge_manual: personInchargeManual,
                                contact_recipient: contactRecipient,
                                phonePersonInCharge: phonePersonInCharge,
                                receiver_manual: recevier_manual,
                                notes: notes,
                                project_location: projectLocation,
                                selected_assets: selectedAsset,
                                isUpdate: isUpdate.val(),
                                idMovement: idMovement.val()
                            },
                            success: function(success) {
                                // Handle success response
                                $('#save-button').attr('type', 'submit');
                                $('#save-button').text('Update');
                                isUpdate.val('update');
                                idMovement.val(success.payload.id);
                                Swal.fire({
                                    title: "Successfully!",
                                    text: "New movement success updated",
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                    showCancelButton: true,
                                    showDenyButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    denyButtonColor: '#4fe90c',
                                    cancelButtonText: "Leave This Page",
                                    confirmButtonText: "Stay In Here",
                                    denyButtonText: 'Make it new again'
                                }).then((result) => {
                                    if (result.isDismissed) {
                                        window.location.href = baseUrl + redirectUrl +
                                            '?status=openMovement';
                                    } else if (result.isDenied) {
                                        location.reload();
                                    } else {
                                        console.log(isUpdate.val());
                                        console.log(idMovement.val());
                                    }
                                });

                            },
                            error: function(error) {
                                Swal.fire({
                                    title: "Failed",
                                    text: error.messages,
                                    icon: "error"
                                });
                            },
                        });
                    }

                }

                // Submit the form if needed
                // $("#myForm").submit();
            });


            function getAddressCompany() {
                $('#company_id').change(function() {
                    var value = $(this).val().split('+');
                    $('#addressCompany').empty(); // Menghapus isi sebelumnya
                    if (value != '') {
                        $('#addressCompany').append('<p id="companyAddress">' + value[1] + '</p>');
                    }
                });
            }

            function addNewFormAddressManual() {
                $('#to_receiver').change(function() {
                    var value = $(this).val();
                    if (value === 'manual') {
                        var form =
                            '<div id="receiverManual" class="form-group {{ $errors->has('receiver_manual') ? ' has-error' : '' }}">' +
                            '<label for="receiver_manual" class="col-md-3 control-label">Entry Receiver Manually</label>' +
                            '<div class="col-md-7 col-sm-12 required">' +
                            '<textarea class="form-control" id="recevier_manual" type="text" name="receiver_manual" aria-label="receiver_manual" id="receiver_manual" required>{{ old('receiver_manual') }}</textarea> ' +
                            '</div>';
                        $('#manualReceiver').append(form);
                    } else {
                        $('#receiverManual').remove();
                    }
                });
            }

            function personChargeManual() {
                $('#person_id').change(function() {
                    var value = $(this).val();
                    if (value === 'manual') {
                        var form =
                            '<div id="personChargeManual" class="form-group {{ $errors->has('to') ? ' has-error' : '' }}">' +
                            '<label for="person_charge_manual" class="col-md-3 control-label">Entry Person In Charge Manually</label>' +
                            '<div class="col-md-7 col-sm-12 required">' +
                            '<textarea class="form-control" type="text" name="person_charge_manual" aria-label="person_charge_manual" id="person_charge_manual" required>{{ old('person_charge_manual') }}</textarea> ' +
                            '</div>';
                        var formPhone =
                            '<div id="phonePersonChargeManual" class="form-group">' +
                            '<label for="phonePersonInCharge" class="col-md-3 control-label">Entry Phone Number Person In Charge Manually</label>' +
                            '<div class="col-md-7 col-sm-12 required">' +
                            '<input class="form-control" type="text" name="phonePersonInCharge" aria-label="phonePersonCharge" id="phonePersonInCharge" required/>' +
                            '</div>';
                        $('#personCharge').append(form);
                        $('#phonePersonCharge').append(formPhone);

                    } else {
                        $('#personChargeManual').remove();
                        $('#phonePersonChargeManual').remove();
                    }
                });
            }

            function getAddressByProject() {
                $('#project_id').change(function() {
                    var value = $(this).val();
                    $('#to_receiver').find('option').not(':first-child, :eq(1)').remove();
                    // $('#person_id').find('option').not(':first-child, :eq(1)').remove();
                    var url = '{{ route('project-movement.getAddressByProjectId') }}';
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: 'json',
                        data: {
                            'project': value
                        },
                        success: function(response) {
                            if (response['data'] != null) {
                                len = response['data'].length;
                            }
                            if (len > 0) {
                                for (let index = 0; index < len; index++) {
                                    var id = response['data'][index].id;
                                    var address = response['data'][index].address;

                                    var option = "";
                                    option = "<option value='" + address + "'>" + address +
                                        "</option>";
                                    $('#to_receiver').append(option);
                                    // $('#person_id').append(option);
                                }
                            }
                        }
                    });
                });
            }
        });
    </script>
@endsection
