<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        /* Add custom styles here */
        /* @page {
            size: 10.125in 9.375in;
            margin: 0.25in;
        } */

        body {
            font-family: 'Arial', sans-serif;
        }

        .invoice-header,
        .invoice-footer {
            border-bottom: 2px solid #ddd;
            padding: 10px 0;
        }

        .item-list th {
            text-align: center;
        }

        .item-list td {
            padding: 4px;
        }

        .blue-text {
            color: rgb(82, 163, 255);
        }

        .white-text {
            color: rgb(155, 155, 155);
            /* -webkit-text-stroke: 0.5px black; */
            /* WebKit/Blink browsers */
            /* text-stroke: 1px black; */
            /* Standard syntax */
        }

        .signature {
            height: 100px;
            /* Adjust the height as needed */
        }

        /* thead {
            background: rgb(209, 209, 209);
        } */

        @media print {

            /* thead {
                background-color: rgb(194, 7, 7) !important;
                -webkit-print-color-adjust: exact;
                /* Specify your desired background color */
            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="btn-print mb-3">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <img src="{{ asset('img/favicon.ico') }}" height="80" width="200" alt="">
            </div>

        </div>
        <!-- Address and Project Info -->
        <div class="row">
            <div class="col-sm-6">
                <strong>Sender:</strong>

                <table class="table table-bordered border border-dark">
                    <tr>
                        <th colspan="2">
                            @if ($from === 'Oceanside Solution LLC')
                                <p class="blue-text">OCEANSIDE<span class="white-text">SOLUTIONS</span></p>
                            @else
                                <p class="blue-text">{{ $from ?? '' }}</p>
                            @endif
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2">{{ $address ?? '-' }}</th>
                    </tr>
                    <tr>
                        <th style="width: 37%;">Contact</th>
                        <td>{{ $person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th style="width: 37%;">Off./Cell</th>
                        <td>{{ $phone ?? '-' }}</td>
                    </tr>
                </table>


            </div>
            <div class="col-sm-6 text-left">
                <strong>Recipient:</strong>
                <table class="table table-bordered border border-dark">
                    {{-- <tr>
                        <th colspan="2">
                            <p class="blue-text">{{ $recipient->first_name . ' ' . $recipient->last_name ?? '' }}</p>
                        </th>
                    </tr> --}}
                    <tr style="height: 114px;">
                        <th colspan="2">{!! nl2br($to) ?? '' !!}</th>
                    </tr>
                    <tr>
                        <th style="width: 37%;">Contact</th>
                        <td>{{ $recipient->first_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th style="width: 37%;">Off./Cell</th>
                        <td>{{ $recipient->phone ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="table table-bordered border border-dark">
                    <tr>
                        <th style="width: 37%;">Project</th>
                        <td>{{ $projectName ?? '' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="table table-bordered border border-dark">
                    <tr>
                        <th style="width: 37%;">Comm. Invoice No.</th>
                        <td>
                            @php
                                $currentDate = date('Ymd');
                                $movement = explode('-', $movementNumber);
                                $movement[1] = str_pad($movement[1], 5, '0', STR_PAD_LEFT);
                            @endphp

                            {{ $currentDate }}/{{ $movementNumber }}/{{ $movement[1] }}
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 37%;">Date</th>
                        <td>
                            @php
                                $dateNow = $chooseDate
                            @endphp
                            {{ $dateNow }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Item List -->
        <div class="row mt-4">
            <div class="col-md-12">
                <table class="table table-bordered item-list">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Description</th>
                            <th>Asset No.</th>
                            <th>Serial No./ Model No.</th>
                            <th>Value for Customs Purposes ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Add your item rows dynamically here -->
                        @foreach ($listAssets as $index => $asset)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td style="text-align: start;">{{ $asset->name }}</td>
                                <td style="text-align: start;">{{ $asset->asset_tag }}</td>
                                <td style="text-align: start;">
                                    {{ $asset->_snipeit_serial_number_15 ?? $asset->_snipeit_serial_number_8 }}</td>
                                <td style="text-align: start;">
                                    ${{ number_format($asset->_snipeit_value_for_customs_purposes_14, 2, '.', ',') }}
                                </td>

                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" style="text-align: end;">Total:</td>
                            <td style="text-align: start;" id="totalCell"></td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Notes and Signature -->
        <strong>Notes:</strong>
        <table class="table table-bordered border border-dark">
            <tr style="height: 100px;">
                <td colspan=5"">
                    {!! nl2br($notes) ?? '' !!}
                </td>
            </tr>
        </table>
        <div class="row">
            <div class="col-sm-4 p-0 pl-3">
                <strong>Issued by:</strong>
                <table class="table table-bordered border border-dark">
                    <tr style="height: 40px;">
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-4 p-0">
                <strong>Signature:</strong>
                <table class="table table-bordered border border-dark">
                    <tr style="height: 40px">
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>
        </div>
        {{-- <div class="row mt-4">
            <div class="col-sm-6">
                <h5>Notes:</h5><br><br>

            </div>
            <div class="col-sm-6 text-right">
                <h5>Signature:</h5><br><br>
                <p>{{ $person ?? '' }}</p>
            </div>
        </div>
        <hr> --}}

    </div>

    <!-- Bootstrap JS and dependencies (optional) -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> <!-- Include jQuery if not already included -->

    <script>
        $(document).ready(function() {
            calculateTotal();

            function calculateTotal() {
                var total = 0;

                // Iterate through each row in the table
                $('.item-list tbody tr').each(function() {
                    // Extract the value from the last cell of the current row
                    var value = parseFloat($(this).find('td:last-child').text().replace(/[^\d.-]/g, '')) ||
                        0;

                    // Add the value to the total
                    total += value;
                });

                // Display the total in the total cell
                $('#totalCell').text('$' + total.toFixed(2)); // Adjust the number of decimal places as needed
            }
        });
    </script>
</body>

</html>
