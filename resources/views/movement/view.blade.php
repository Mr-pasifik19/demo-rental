@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($isShowMovement === true)
        {{ trans('admin/movement/general.view') }} : {{ $movement->project->project_name }}
        ({{ $movement->movement_number }})
    @else
        View Project : {{ $project->project_name }}
    @endif
    @parent
@endsection
@push('css')
    <style>
        .new-status-row {
            padding-top: 10px;
            /* Adjust the value as needed */
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
{{-- Right header --}}
@section('header_right')
@endsection

{{-- Page content --}}
@section('content')
    <div class="container-fluid">
        @if ($isShowMovement === true)
            <div class="box">
                <div class="box-body" style="padding: 50px;">
                    <table class="table">
                        <tr>
                            <th>Project Name</th>
                            <td>: {{ $movement->project->project_name }}</td>
                        </tr>
                        <tr>
                            <th>Project Number</th>
                            <td>: {{ $movement->project->project_number }}</td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>: {{ $movement->location->name }}</td>
                        </tr>
                        <tr>
                            <th>Movement Status</th>
                            <td>:
                                <strong
                                    class="{{ $movement->status->name === 'Booked'
                                        ? 'text-orange'
                                        : ($movement->status->name === 'In Project'
                                            ? 'text-green'
                                            : ($movement->status->name === 'Available'
                                                ? 'text-blue'
                                                : ($movement->status->name === 'Archived'
                                                    ? 'text-orange'
                                                    : ($movement->status->name == 'Out for Calibration'
                                                        ? 'text-red'
                                                        : ($movement->status->name == 'Out for Service'
                                                            ? 'text-orange'
                                                            : 'text-red'))))) }}">
                                    {{ $movement->status->name }}
                                </strong>
                            </td>

                        </tr>
                        <tr>
                            <th>Company Branch</th>
                            <td>: {{ $movement->company->company_name }}</td>
                        </tr>
                        <tr>
                            <th>To Receiver</th>
                            <td>: {{ $movement->to }}</td>
                        </tr>
                        @if ($movement->phone_person_in_charge === null)
                            @php
                                $person = \App\Models\User::find($movement->person_charge);
                            @endphp
                            <tr>
                                <th>Person In Charge</th>
                                <td>: {{ $person->full_name }} (Phone: <strong>{{ $person->phone }}</strong>)</td>
                            </tr>
                        @else
                            <tr>
                                <th>Person In Charge</th>
                                <td>: {{ $movement->person_charge }}
                                    (Phone: <strong>{{ $movement->phone_person_in_charge }}</strong>)</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Notes</th>
                            <td>: {{ $movement->notes ?? '-' }}</td>
                        </tr>
                        {{-- <tr>
                            <th>Category Movement</th>
                            <td>: {{ $movement->category_movement == 'INMOV' ? 'OPEN MOVEMENT' : 'MOVEMENT RETURN' }}</td>
                        </tr> --}}
                        @if ($movement->datetime === null)
                            <tr>
                                <th>Date Time Returned</th>
                                <td>: {{ $movement->datetime_specific ?? '-' }}</td>
                            </tr>
                        @else
                            <tr>
                                <th>Date Time Returned</th>
                                <td>: {{ $movement->datetime ?? '-' }}</td>
                            </tr>
                        @endif
                    </table>
                    @if ($movement->is_return === 0)
                        <div class="pull-right" style="padding-left: 5px;">
                            <span data-tooltip="true" title="Return this movement">
                                <a href="{{ route('movement.returnMovementPartial', $movement->id) }}"
                                    class="btn btn-sm btn-danger w-50 overflow-hidden">
                                    <i class="fa fa-arrow-turn-down"></i> Return This Movement
                                </a>
                            </span>
                        </div>
                    @endif
                    <div class="pull-right" style="padding-left: 5px;">
                        <span data-tooltip="true" title="Change status asset movement">
                            <a href="#" class="btn btn-sm btn-info w-50 overflow-hidden" data-toggle="modal"
                                data-target="#modal-Status">
                                <i class="fa fa-arrow-right-arrow-left"></i> Change Movement Status
                            </a>
                        </span>
                    </div>
                    <div class="pull-right" style="padding-left: 5px;">

                        <span data-tooltip="true" title="Print invoice"> <button type="button"
                                class="btn btn-sm btn-primary w-50 overflow-hidden" data-toggle="modal"
                                data-target="#modal-invoice">
                                <i class="fa fa-print"></i>
                                Print Invoice
                            </button></span>

                    </div>

                    {{-- Modal Print Invoice --}}
                    <div class="modal fade" id="modal-invoice">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Print Invoice</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('movement.invoice.index') }}" method="get" target="_blank">
                                    @csrf
                                    <input type="hidden" name="movement_id" id="movement_id_modal"
                                        value="{{ $movement->id }}">

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
                                            <label for="specific_date_text" class="col-md-3 col-form-label">Specific Date &
                                                Time:</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control flatpickr"
                                                    name="specific_date_text" id="specific_date_text"
                                                    placeholder="YYYY-MM-DD HH:mm">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row mb-3">
                                            <label for="format_date" class="col-md-3 col-form-label">Format
                                                Date
                                                <code>*Optional</code></label>
                                            <div class="col-md-7 col-sm-12">
                                                <select name="format_date" id="format_date"
                                                    class="form-control select2 h-100" style="width: 100%;">
                                                    <option value="d-M-y">Default (d-M-Y)</option>
                                                    {{-- <option value="manual">Entry Manually</option> --}}
                                                    @foreach (\App\Models\FormatDate::all() as $formatDates)
                                                        <option value="{{ $formatDates->format }}">
                                                            {{ $formatDates->format }}</option>
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
                                                <label for="" class="col-form-label">Format
                                                    Sample:</label><br>
                                                <code>d: Day in two digits (01 to 31)</code><br>
                                                <code>D: Short day name (Sun to Sat)</code><br>
                                                <code>l (lowercase L): Full text day (Sunday to
                                                    Saturday)</code><br>
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
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                                    id="close-modal-print" aria-label="Close">Close</button>
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
                </div>
            </div>
            <div class="modal" id="modal-formatDate" data-backdrop="static">
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
            <div class="box">
                <div class="box-body" style="overflow: auto;">
                    <table id="tableMovement" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Asset Name</th>
                                <th>Asset Number</th>
                                <th>Location</th>
                                <th>Last Checkout</th>
                                <th>Notes</th>
                                <th>Asset Status</th>
                                <th>Is Asset <br> Return?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assets as $index => $asset)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><a
                                            href="{{ route('hardware.show', $asset->id_asset) }}">{{ $asset->asset_name }}</a>
                                    </td>
                                    <td>{{ $asset->asset_tag }}</td>
                                    <td>{{ $asset->location }}</td>
                                    <td>{{ $asset->last_checkout }}</td>
                                    <td style="max-width: 150px;">{!! nl2br(wordwrap($asset->notes, 20, "\n", true)) !!}</td>
                                    <td>
                                        <strong
                                            class="{{ $asset->status === 'Booked'
                                                ? 'text-orange'
                                                : ($asset->status === 'In Project'
                                                    ? 'text-green'
                                                    : ($asset->status === 'Available'
                                                        ? 'text-blue'
                                                        : ($asset->status === 'Archived'
                                                            ? 'text-orange'
                                                            : ($asset->status == 'Out for Calibration'
                                                                ? 'text-red'
                                                                : ($asset->status == 'Out for Service'
                                                                    ? 'text-orange'
                                                                    : 'text-red'))))) }}">{{ $asset->status }}</strong>
                                    </td>
                                    <td>
                                        <span
                                            class="{{ $asset->return_asset == 0 ? 'text-blue' : 'text-green' }} text-bold">{{ $asset->return_asset == 0 ? 'Not Yet' : 'Yes' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">History Movements</h3>
                </div>
                <div class="box-body" style="overflow: auto;">
                    <table id="tableHistoryMovement" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Activity</th>
                                <th>Date Time</th>
                        </thead>
                        <tbody>
                            @foreach ($movementLog as $index => $asset)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $asset->activity }}</td>
                                    <td>{{ $asset->created_at }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Modal Status --}}
            <div class="modal fade" id="modal-Status" data-backdrop="static">
                <form action="{{ route('movement.changeStatus') }}" method="post">
                    @csrf
                    @method('post')
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Confirmation Change Status Movement</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-3"><strong>Current Status</strong></div>
                                    <div class="col-md-9">: {{ $movement->status->name }}</div>
                                </div>
                                <div class="row new-status-row">
                                    <div class="col-md-3"><strong>New Status</strong></div>
                                    <div class="col-md-9">
                                        <select name="status_movement" id="status_movement" class="form-control select2"
                                            style="width: 100%;" required>
                                            <option value="">Select Status</option>
                                            @foreach (\App\Models\StatusLabel::all() as $status)
                                                <option value="{{ $status->id . '+' . $status->name }}">
                                                    {{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                        {!! $errors->first(
                                            'status_movement',
                                            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        aria-hidden="true"></i> :message</span>',
                                        ) !!}
                                    </div>
                                </div>
                                <br>

                                <div class="row">
                                    <div class="col-md-3"><strong>Date Selection:</strong></div>
                                    <div class="col-md-9">
                                        <select class="form-control" id="execution_type_status" name="execution_type"
                                            aria-label="Date Selection" style="width: 100%;" required>
                                            <option value="current_date_time" id="current_date_time"
                                                name="current_date_time">
                                                Use Current Date & Time
                                            </option>
                                            <option value="specific_date" id="specific_date" name="specific_date">
                                                Specific Date & Time
                                            </option>
                                        </select>

                                    </div>
                                </div>
                                <div class="row" id="specific_date_input_status" style="display:none;">
                                    <label for="specific_date_text" class="col-md-3 col-form-label"></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control flatpickr pickr_status"
                                            name="specific_date_text" id="specific_date_text_status"
                                            placeholder="YYYY-MM-DD HH:mm">
                                    </div>
                                </div>
                                <br>

                            </div>


                            <div class="modal-footer">
                                <div class="row">
                                    <div class="col-sm-10">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                            aria-label="Close">
                                            Close
                                        </button>
                                    </div>
                                    <div class="col-sm-1 ml-auto">
                                        <!-- Added ml-auto class to align the button to the right -->
                                        <input type="hidden" name="id" value="{{ $movement->id }}">
                                        <button type="submit" class="btn btn-success">
                                            Save
                                        </button>

                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </form>
            </div>
        @else
            <div class="box">
                <div class="box-body" style="padding: 50px;">
                    <table class="table">
                        <tr>
                            <th>Project Name</th>
                            <td>: {{ $project->project_name }}</td>
                        </tr>
                        <tr>
                            <th>Project Number</th>
                            <td>: {{ $project->project_number }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="box">
                <div class="box-body" style="overflow: auto;">
                    <table id="tableMovement" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Movement Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Company</th>
                                <th>Person In Charge</th>
                                <th>To</th>
                                <th>Status Movement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movement as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><a href="{{ route('movement.show', $data->id) }}"
                                            class="text-blue">{{ $data->movement_number }}</a></td>
                                    <td>{{ $data->location->name }}</td>
                                    <td>{{ $data->status->name }}</td>
                                    <td>{{ $data->company->company_name }}</td>
                                    <td>{{ $data->person_charge }}</td>
                                    <td>{{ $data->to }}</td>
                                    <td><span
                                            class="{{ $data->is_return == '0' ? 'text-blue' : 'text-green' }} text-bold">{{ $data->is_return == '0' ? 'Open' : 'Return' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>


@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.pickr_status', {
                enableTime: true,
                dateFormat: "Y-m-d H:i:s",
                defaultDate: "today",
            });
        });
        $(function() {

            $('#execution_type_status').on('change', function() {
                var specificDateInput = $('#specific_date_input_status');
                var specificDateField = $('#specific_date_text_status');

                if (this.value === 'specific_date') {
                    specificDateInput.show();

                } else {
                    specificDateInput.hide();
                    specificDateField.val(getCurrentDateTimeStatus());
                }
            });

            function getCurrentDateTimeStatus() {
                var now = new Date();
                var formattedDate = moment(now).tz('America/New_York').format(
                    'YYYY-MM-DD HH:mm:ss'
                );

                return formattedDate;
            }
            $('#execution_type').on('change', function() {
                var specificDateInput = $('#specific_date_input');
                var specificDateField = $('#specific_date_text');

                if (this.value === 'specific_date') {
                    specificDateInput.show();

                } else {2
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
            $('#close-format-modal').click(function() {
                $('#modal-formatDate').modal('hide');
            });


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

                                var formatSelect = $('#format_date');
                                formatSelect.append('<option value="' + value +
                                    '" selected="selected">' + value + '</option>');
                                formatSelect.val(
                                value); // Set the value of the select element
                                formatSelect.trigger('change');

                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.success(response.messages);
                            },
                            error: function(response) {
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.error(response.messages);

                                formatDate.val("");
                                modal.modal("hide");
                            }
                        });
                    }
                });
            });

        });
    </script>
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function() {

            $('#tableMovement').DataTable({
                // dom: 'Qlfrtip'
                responsive: true
            });
            $('#tableHistoryMovement').DataTable({
                // dom: 'Qlfrtip'
                responsive: true,
                columnDefs: [{
                        width: '10%',
                        targets: 0
                    },
                    {
                        width: '90%',
                        targets: 1
                    },
                    {
                        width: '10%',
                        targets: 2
                    },
                    // { width: '70%', targets: 3 } ,
                    // { width: '50%', targets: 4 } ,
                ]
            });
        });
    </script>
    @include ('partials.bootstrap-table')
@endsection
