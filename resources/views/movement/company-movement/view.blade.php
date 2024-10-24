@extends('layouts.default')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('title0')
    @if (Request::get('company_id') && $company)
        {{ $company->name }}
    @endif

    @if (Request::get('status'))
        @if (Request::get('status') == 'Pending')
            {{ trans('general.pending') }}
        @elseif (Request::get('status') == 'RTD')
            {{ trans('general.ready_to_deploy') }}
        @elseif (Request::get('status') == 'Deployed')
            {{ trans('general.deployed') }}
        @elseif (Request::get('status') == 'Undeployable')
            {{ trans('general.undeployable') }}
        @elseif (Request::get('status') == 'Deployable')
            {{ trans('general.ready_to_deploy') }}
        @elseif (Request::get('status') == 'Requestable')
            {{ trans('admin/hardware/general.requestable') }}
        @elseif (Request::get('status') == 'Archived')
            {{ trans('general.archived') }}
        @elseif (Request::get('status') == 'Deleted')
            {{ trans('general.deleted') }}
        @elseif (Request::get('status') == 'byod')
            {{ trans('general.byod') }}
        @endif
    @else
        {{ trans('general.all') }} Movement By
    @endif
    {{ trans('general.company_movement') }}

    @if (Request::has('order_number'))
        : Order #{{ Request::get('order_number') }}
    @endif
@endsection

@section('title')
    @yield('title0') @parent
@endsection
@section('header_right')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="box">
            <div class="box-body" style="overflow: auto;">
                <form action="{{ route('company-movement.showAssingnedMovement', $companyBranch->id) }}" method="GET">
                    @csrf
                    <div class="form-group pull-right" style="padding-left: 5px;">
                        <button type="submit" class="btn btn-sm btn-info" style="height: 35px;"><i
                                class="fas fa-search"></i> Filter</button>
                    </div>
                    <div class="form-group pull-right">
                        <input type="text" name="searchMovement" id="search" class="form-control"
                            value="{{ $search ?? old('searchMovement') }}" placeholder="Search...">
                    </div>
                    <div class="form-group pull-right" style="padding-right: 5px;">
                        <select name="selectCategory" id="" class="form-control select2">
                            <option value="">Select Category Filter</option>
                            <option value="projectNumber">By Project Number</option>
                            <option value="projectName">By Project Name</option>
                            <option value="movementNumber">By Movement Number</option>
                        </select>
                    </div>
                </form>
                <table class="display nowrap table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <th>Movement Number</th>
                            <th>Movement Category</th>
                            <th>Movement Status</th>
                            <th>Location</th>
                            <th>Branch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movementData as $index => $movementItem)
                            @php
                                $numberMovementArray = explode(',', $movementItem->number_movement);
                                $locationArray = explode(',', $movementItem->location_movement);
                                $companyArray = explode(',', $movementItem->company_name);
                                $companyIdsArray = explode(',', $movementItem->company_id);
                                $movementIdsArray = explode(',', $movementItem->movement_ids);
                                $categoryMovementArray = explode(',', $movementItem->is_return);
                            @endphp
                            @foreach ($numberMovementArray as $key => $number)
                                <tr>
                                    @if ($loop->first)
                                        <td rowspan="{{ count($numberMovementArray) }}">{{ $index + 1 }}</td>
                                        <td rowspan="{{ count($numberMovementArray) }}">
                                            <a href="{{ route('movement.showProject', $movementItem->project_id) }}"
                                                class="text-blue">{{ strtoupper($movementItem->project_number) }}</a>
                                        </td>
                                        <td rowspan="{{ count($numberMovementArray) }}">{{ $movementItem->project_name }}
                                        </td>
                                    @endif
                                    <td>
                                        <a href="{{ route('movement.show', $movementIdsArray[$key]) }}" class="text-blue">
                                            <i class="fas fa-save fa-fw"></i>
                                            <span>{{ $number }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        @if (isset($movementIdsArray[$key]) && isset($categoryMovementMap[$movementIdsArray[$key]]))
                                            {{ $categoryMovementArray[$key] === '0' ? 'Open Movement' : 'Returned' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($movementIdsArray[$key]) && isset($statusMap[$movementIdsArray[$key]]))
                                            @if (str_contains($statusMap[$movementIdsArray[$key]], 'Booked'))
                                                <span
                                                    class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'In Project'))
                                                <span
                                                    class="text-green text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Available'))
                                                <span
                                                    class="text-blue text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Archived'))
                                                <span
                                                    class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Out for Calibration'))
                                                <span
                                                    class="text-red text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Damaged'))
                                                <span
                                                    class="text-red text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @else
                                                <span
                                                    class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($movementIdsArray[$key]) && isset($locationMap[$movementIdsArray[$key]]))
                                            {{ $locationArray[$key] }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($movementIdsArray[$key]) && isset($companyMap[$movementIdsArray[$key]]))
                                            {{ $companyArray[$key] }}
                                        @endif
                                    </td>

                                    <td>
                                        <div class="row">
                                            {{-- <div class="col-sm-2 p-2">
                                                    <span data-tooltip="true" title="Delete movement"><button type="button"
                                                            class="btn btn-sm btn-danger" data-toggle="modal"
                                                            data-target="#modal-Delete{{ $movementIdsArray[$key] }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button></span>
                                                </div> --}}
                                            <div class="col-sm-2 p-2 mb-5 mb-sm-0"
                                                style="margin-right: {{ $categoryMovementArray[$key] === '0' ? '10px' : '10px' }}; margin-bottom: 5px; @media (min-width: 576px) { margin-bottom: 0; }">
                                                <span data-tooltip="true" title="Change status">
                                                    <a href="#" class="btn btn-sm btn-info" data-toggle="modal"
                                                        data-target="#modal-Status{{ $movementIdsArray[$key] }}">
                                                        <i class="fa fa-arrow-right-arrow-left"></i>
                                                    </a>
                                                </span>
                                            </div>

                                            @if (isset($movementIdsArray[$key]) && isset($categoryMovementMap[$movementIdsArray[$key]]))
                                                @if ($categoryMovementArray[$key] === '0')
                                                    <div class="col-sm-2 p-2 mb-5 mb-sm-0"
                                                        style="margin-right: 10px; margin-bottom: 5px; @media (min-width: 576px) { margin-bottom: 0; }">
                                                        <span data-tooltip="true" title="Return this movement {$number }}"
                                                            data-html="true" data-container="body" data-placement="top"
                                                            style="width: 200px;">
                                                            <a href="{{ route('movement.returnMovementPartial', $movementIdsArray[$key]) }}"
                                                                class="btn btn-sm btn-warning">
                                                                <i class="fa fa-arrow-turn-down"></i>
                                                            </a>
                                                        </span>
                                                    </div>
                                                @endif
                                            @endif



                                            <div class="col-sm-2 p-2">
                                                <span data-tooltip="true" title="Print invoice"> <a href="#"
                                                        class="btn btn-sm btn-primary" data-toggle="modal"
                                                        data-target="#modal-invoice{{ $movementIdsArray[$key] }}">
                                                        <i class="fa fa-print"></i>
                                                    </a></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                {{-- MODAL CHANGE STATUS --}}
                                <div class="modal fade" id="modal-Status{{ $movementIdsArray[$key] }}"
                                    data-backdrop="static">
                                    <form action="{{ route('movement.changeStatus') }}" method="post">
                                        @csrf
                                        @method('post')
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Confirmation Change Status Movement</h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-3"><strong>Current Status</strong></div>
                                                        <div class="col-md-9" id="current-status">
                                                            @if (isset($movementIdsArray[$key]) && isset($statusMap[$movementIdsArray[$key]]))
                                                                @if (str_contains($statusMap[$movementIdsArray[$key]], 'Booked'))
                                                                    <span
                                                                        class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'In Project'))
                                                                    <span
                                                                        class="text-green text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Available'))
                                                                    <span
                                                                        class="text-blue text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Archived'))
                                                                    <span
                                                                        class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Out for Calibration'))
                                                                    <span
                                                                        class="text-red text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @elseif (str_contains($statusMap[$movementIdsArray[$key]], 'Damaged'))
                                                                    <span
                                                                        class="text-red text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @else
                                                                    <span
                                                                        class="text-orange text-bold">{{ $statusMap[$movementIdsArray[$key]] }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <br>

                                                    <div class="row">
                                                        <div class="col-md-3"><strong>Movement Number</strong></div>
                                                        <div class="col-md-9">: {{ $number }}</div>
                                                    </div>
                                                    <br>

                                                    <input type="hidden" name="id" id="id"
                                                        value="{{ $movementIdsArray[$key] }}">
                                                    <div class="row new-status-row">
                                                        <div class="col-md-3"><strong>New Status</strong></div>
                                                        <div class="col-md-9">
                                                            <select name="status_movement" id="status_movement"
                                                                class="form-control select2" style="width: 100%;"
                                                                required>
                                                                <option value="">Select Status</option>
                                                                @foreach (\App\Models\StatusLabel::all() as $status)
                                                                    <option value="{{ $status->id }}">
                                                                        {{ $status->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            {!! $errors->first(
                                                                'status_movement',
                                                                '<span class="alert-msg"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    :message</span>',
                                                            ) !!}
                                                        </div>
                                                    </div>
                                                    <br>

                                                    <div class="row">
                                                        <div class="col-md-3"><strong>Date Selection:</strong></div>
                                                        <div class="col-md-9">
                                                            <select class="form-control" id="execution_type_status"
                                                                name="execution_type" aria-label="Date Selection"
                                                                data-row-id="{{ $movementIdsArray[$key] }}"
                                                                style="width: 100%;" required>
                                                                <option value="current_date_time" id="current_date_time"
                                                                    name="current_date_time">
                                                                    Use Current Date & Time
                                                                </option>
                                                                <option value="specific_date" id="specific_date"
                                                                    name="specific_date">
                                                                    Specific Date & Time
                                                                </option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="row"
                                                        id="specific_date_input_status{{ $movementIdsArray[$key] }}"
                                                        style="display:none;">
                                                        <label for="specific_date_text{{ $movementIdsArray[$key] }}"
                                                            class="col-md-3 col-form-label"></label>
                                                        <div class="col-md-9">
                                                            <input type="text"
                                                                class="form-control flatpickr pickr_status"
                                                                name="specific_date_text"
                                                                id="specific_date_text_status{{ $movementIdsArray[$key] }}"
                                                                placeholder="YYYY-MM-DD HH:mm">
                                                        </div>
                                                    </div>
                                                    <br>
                                                </div>
                                                <div class="modal-footer">
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal" aria-label="Close">
                                                                Close
                                                            </button>
                                                        </div>
                                                        <div class="col-sm-1 ml-auto">
                                                            <!-- Added ml-auto class to align the button to the right -->
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
                                {{-- Modal Print Invoice --}}
                                <div class="modal fade" id="modal-invoice{{ $movementIdsArray[$key] }}">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Print Invoice</h4>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('movement.invoice.index') }}" method="get"
                                                target="_blank">
                                                @csrf
                                                <input type="hidden" name="movement_id" id="movement_id_modal"
                                                    value="{{ $movementIdsArray[$key] }}">

                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <label for="execution_type"
                                                            class="col-md-3 col-form-label"><strong>Choose Date
                                                                :</strong></label>
                                                        <div class="col-md-9">
                                                            <select class="form-control" id="execution_type"
                                                                aria-label="Date Selection" name="choose_date">
                                                                <option value="current_date_time">Use Current Date</option>
                                                                <option value="specific_date">Specific Date</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class="row mb-3" id="specific_date_input" style="display:none;">
                                                        <label for="specific_date_text"
                                                            class="col-md-3 col-form-label">Specific Date & Time:</label>
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
                                                            <button type="button" name="createNewFormat"
                                                                class="btn btn-sm btn-primary"
                                                                data-index="{{ $movementIdsArray[$key] }}">{{ trans('button.new') }}</button>
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
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal" id="close-modal-print"
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
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" align="center">No data available in table</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{-- Create Format Date --}}
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

                <div class="row w-100">
                    <div class="col-md-6">
                        <div class="justify-content-center" style="margin-top:20px;">
                            <p>Showing 1 to {{ count($movementData) }} project movement</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="pagination-movement">
                            <ul class="pagination pull-right">
                                <li class="page-item {{ $movementData->currentPage() == 1 ? 'disabled' : '' }}">
                                    <a href="page-link" href="{{ $movementData->previousPageUrl() }}"
                                        tabindex="-1">Previous</a>
                                </li>
                                @for ($i = 1; $i <= $movementData->lastPage(); $i++)
                                    @if ($i = $movementData->currentPage())
                                        <li class="page-item active">
                                            <a href="#" class="page-link">{{ $i }}</a>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="{{ $movementData->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endif
                                @endfor
                                @if ($movementData->currentPage() != $movementData->lastPage())
                                    <li class="page-item {{ count($movementData) < 3 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $movementData->nextPageUrl() }}">Next</a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
            $('[id^="execution_type_status"]').on('change', function() {
                var idValue = $(this).data('row-id');
                var specificDateInput = $('#specific_date_input_status' + idValue);
                var specificDateField = $('#specific_date_text_status' + idValue);

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
            $('#close-format-modal').click(function() {
                $('#modal-formatDate').modal('hide');
            });

            $('button[name="createNewFormat"]').click(function() {
                var index = $(this).data('index');
                $('#modal-formatDate').data('index', index);
                $('#modal-formatDate').modal('show');
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
                                formatSelect.trigger('change');
                                //
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.success(response.messages);
                                //
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

        });
    </script>
    @include('partials.bootstrap-table')
@endsection
