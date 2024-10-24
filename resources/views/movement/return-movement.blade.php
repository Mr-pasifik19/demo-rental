@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Return Movement
    @parent
@endsection
@push('link-page')
    <a href="{{ route('movement.show', $movement->id) }}" class="text-blue"> {{ $movement->movement_number }}</a>
@endpush
@push('css')
    <style>
        .new-status-row {
            padding-top: 10px;
            /* Adjust the value as needed */
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    {{--
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
{{-- Right header --}}
@section('header_right')
@endsection
@section('content')
    <div class="box box-default">
        <div class="box-header with-border" style="width:30%; margin-left:auto; margin-right:auto; text-align:center;">
            <div class="box-body">
                <div style="padding-top: 10px">
                    <div id="reader" class="col-md-6" style="width:100%"></div>
                </div>
            </div>
        </div>
        <div class="conatiner-fluid">
            <div class="box box-default">
                <div class="box-body">
                    <table id="tableMovement" class="display nowrap table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Asset Number</td>
                                <td>Asset Name</td>
                                <td>Change Asset Status</td>
                                <td>Is Asset Returned</td>
                                <td>Is Asset Scanned</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assets as $index => $asset)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $asset->asset_tag }}</td>
                                    <td><a href="{{ route('hardware.show', $asset->id_asset) }}"
                                            class="text-blue">{{ $asset->asset_name }}</a></td>
                                    <td>
                                        <input type="hidden" value="{{ $asset->id_asset }}"
                                            name="id_asset{{ $index }}">
                                        <select name="status" id="status_{{ $index }}"
                                            class="custom-select form-control" data-asset="{{ $asset->id_asset }}"
                                            data-movement="{{ $movement->id }}">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->id }}"
                                                    {{ $status->name === 'Available' ? 'selected' : '' }}>
                                                    {{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td align="center">
                                        <select name="select_return{{ $index }}"
                                            id="select_return_{{ $index }}" class="form-control">
                                            <option value="yes" {{ $asset->isReturn === 1 ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="no" {{ $asset->isReturn === 0 ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </td>
                                    <td align="center">
                                        <i class="fas fa-x text-danger"></i>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if (count($assetMovement->where('movement_id', $movement->id)->where('is_return', 0)) > 0)
            <div class="box box-default">
                <div class="box-header with-border"
                    style="width: 100%; margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="box-body">
                        <div class="container">
                            <form class="container">
                                <div class="row">
                                    <label for="execution_type" class="col-md-3 col-form-label"><strong>Date Selection:</strong></label>
                                    <div class="col-md-3">
                                        <select class="form-control" id="execution_type" aria-label="Date Selection">
                                            <option value="current_date_time">Use Current Date & Time</option>
                                            <option value="specific_date">Specific Date & Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" id="specific_date_input" style="display:none;">
                                    <label for="specific_date_text" class="col-md-3 col-form-label"></label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control flatpickr" name="specific_date_text" id="specific_date_text" placeholder="YYYY-MM-DD HH:mm">
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        
                        
                    </div>
                </div>

            </div>
            <div class="box-footer text-right">
                <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                <button type="submit" accesskey="s" class="btn btn-primary" id="save-button"><i
                        class="fas fa-check icon-white" aria-hidden="true"></i>
                    {{ trans('general.save') }}</button>
            </div>
        @endif
    </div>
@endsection

@push('js')


    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-timezone-with-data.min.js') }}"></script>
    {{--
<script src="{{ asset('js/pikaday.js') }}"></script> --}}
    <script src="{{ asset('js/scan.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endpush
@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        var baseUrl = "{{ url('/') }}";
        var redirectUrl = '/movement';
        const formatsToSupport = [
            Html5QrcodeSupportedFormats.QR_CODE,
        ];
        const html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 3,
            qrbox: {
                width: 250,
                height: 250
            },
            formatsToSupport: formatsToSupport
        });
        var scannedAsset = [];

        function onScanSuccess(qrCodeMessage) {
            // console.log("QR Code detected and processed: " + qrCodeMessage);

            // Loop through table rows
            var tableRows = document.querySelector("#tableMovement tbody").getElementsByTagName("tr");

            for (var i = 0; i < tableRows.length; i++) {
                var assetTag = tableRows[i].getElementsByTagName("td")[1].innerText.trim();
                var idAsset = tableRows[i].getElementsByTagName('td')[3].querySelector('input').value;
                // Check if asset tag matches QR code message
                if (assetTag === qrCodeMessage) {
                    // Update the Scanned column with the check icon
                    var scannedColumn = tableRows[i].getElementsByTagName("td")[5];
                    scannedColumn.innerHTML = '<i class="fas fa-check text-green"></i>';

                    if (scannedAsset.includes(idAsset)) {
                        // Display an error message if the asset has already been scanned
                    } else {
                        scannedAsset.push(idAsset);
                    }

                    // Exit the loop since we found a match
                    break;
                }
            }

            var allRowsScanned = true;
            for (var j = 0; j < tableRows.length; j++) {
                var scannedColumn = tableRows[j].getElementsByTagName("td")[5];
                if (scannedColumn.innerHTML === "") {
                    allRowsScanned = false;
                    scannedColumn.innerHTML = '<i class="fas fa-x text-danger"></i>';
                }
            }
            if (allRowsScanned) {
                // console.log("All rows have been scanned.");
            }
        }



        html5QrcodeScanner.render(onScanSuccess);
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.flatpickr', {
                enableTime: true,
                dateFormat: "Y-m-d H:i:s",
                defaultDate: "today",
            });
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
                'YYYY-MM-DD HH:mm:ss'
            );

            return formattedDate;
        }


        $(document).ready(function() {
            $('#tableMovement').DataTable({
                responsive: true
            });

            changeStatus();

            function changeStatus() {
                var rowCount = $('#tableMovement tbody tr').length;
                for (var row = 0; row < rowCount; row++) {
                    $('#status_' + row).change(function() {
                        let idAsset = $(this).data('asset');
                        var idStatus = $(this).val();
                        var movementId = $(this).data('movement');
                        $.ajax({
                            type: 'GET',
                            dataType: 'JSON',
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            },
                            url: '{{ route('api.assets.changeStatus') }}',
                            data: {
                                idAsset: idAsset,
                                status_id: idStatus,
                                movementId: movementId
                            },
                            success: function(t) {
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.success(t.messages);

                            },
                            error: function(t) {
                                toastr.options.closeButton = true;
                                toastr.options.closeMethod = 'fadeOut';
                                toastr.options.closeDuration = 100;
                                toastr.error(t.messages);
                            }
                        });
                    });
                }
            }
        });



        $('#save-button').click(function() {
            var scanned_join_asset = scannedAsset.join(",");
            var executionType = $('#execution_type').val();
            var specificDate = $('#specific_date_text').val();

            var selectedReturn = [];
            var changesStatusAssests = [];
            $('[name^="select_return"]').each(function(index, element) {
                // Get the selected value
                var selectedValue = $(element).val();
                // Get the associated asset ID from the hidden input
                var assetId = $('[name="id_asset' + index + '"]').val();

                // Add the status and asset ID pair to the array
                selectedReturn.push({
                    assetId: assetId,
                    status: selectedValue
                });

            });
            $('[name^="status"]').each(function(index, element) {
                // Get the selected value
                var selectedValue = $(element).val();
                // Get the associated asset ID from the hidden input
                var assetId = $('[name="id_asset' + index + '"]').val();

                // Add the status and asset ID pair to the array
                changesStatusAssests.push({
                    assetId: assetId,
                    status: selectedValue
                });

            });

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                url: '{{ route('movement.changeDatetime') }}',
                data: {
                    movement_id: '{{ $movement->id }}',
                    execution_type: executionType,
                    specific_date: specificDate,
                    scannedAsset: scanned_join_asset,
                    select_return: JSON.stringify({
                        statusData: selectedReturn
                    }),
                    status_asset: JSON.stringify({
                        statusAsset: changesStatusAssests
                    }) // Removed the semicolon
                },
                success: function(t) {

                    toastr.options.closeButton = true;
                    toastr.options.closeMethod = 'fadeOut';
                    toastr.options.closeDuration = 100;
                    toastr.success(t.messages);

                    if (t.payload != null) {
                        window.location.href = baseUrl + redirectUrl + '?status=' + t.payload;
                    }else{
                        location.reload();
                    }
                },
                error: function(t) {
                    toastr.options.closeButton = true;
                    toastr.options.closeMethod = 'fadeOut';
                    toastr.options.closeDuration = 100;
                    toastr.error(t.messages);
                    location.reload(true);

                }
            });
        });
    </script>
    @include ('partials.bootstrap-table')
@endsection
