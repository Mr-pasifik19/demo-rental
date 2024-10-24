<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Labels QR Code</title>

</head>

<style>
    @page {
        size: 3.125in 13.375in;
        /* Define the page size for printing 5 QR codes per page */
        margin: 0.25in;
    }

    /* Rest of your CSS styles */

    .qrCode {
        /* padding-top: 10px; */
        display: flex;
        justify-content: center;
        align-content: center;
        align-items: center;
    }

    /* Add any other styles as needed */
</style>


<style>
    {{-- body { --}} {{--    font-family: arial, helvetica, sans-serif; --}} {{--    width: {{ $settings->labels_pagewidth }}in; --}} {{--    height: {{ $settings->labels_pageheight }}in; --}} {{--    margin: {{ $settings->labels_pmargin_top }}in {{ $settings->labels_pmargin_right }}in {{ $settings->labels_pmargin_bottom }}in {{ $settings->labels_pmargin_left }}in; --}} {{--    font-size: {{ $settings->labels_fontsize }}pt; --}} {{-- } --}} {{-- .label { --}} {{--    width: {{ $settings->labels_width }}in; --}} {{--    height: {{ $settings->labels_height }}in; --}} {{--    padding: 0in; --}} {{--    margin-right: {{ $settings->labels_display_sgutter }}in; /* the gutter */ --}} {{--    margin-bottom: {{ $settings->labels_display_bgutter }}in; --}} {{--    display: inline-block; --}} {{--    overflow: hidden; --}} {{-- } --}} {{-- .page-break  { --}} {{--    page-break-after:always; --}} {{-- } --}} {{-- div.qr_img { --}} {{--    width: {{ $qr_size }}in; --}} {{--    height: {{ $qr_size }}in; --}} {{--    float: left; --}} {{--    display: inline-flex; --}} {{--    padding-right: .15in; --}} {{-- } --}} {{-- img.qr_img { --}} {{--    width: 120.79%; --}} {{--    height: 120.79%; --}} {{--    margin-top: -6.9%; --}} {{--    margin-left: -6.9%; --}} {{--    padding-bottom: .04in; --}} {{-- } --}} {{-- img.barcode { --}} {{--    display:block; --}} {{--    margin-top:-14px; --}} {{--    width: 100%; --}} {{-- } --}} {{-- div.label-logo { --}} {{--    float: right; --}} {{--    display: inline-block; --}} {{-- } --}} {{-- img.label-logo { --}} {{--    height: 0.5in; --}} {{-- } --}} {{-- .qr_text { --}} {{--    width: {{ $settings->labels_width }}in; --}} {{--    height: {{ $settings->labels_height }}in; --}} {{--    padding-top: {{$settings->labels_display_bgutter}}in; --}} {{--    font-family: arial, helvetica, sans-serif; --}} {{--    font-size: {{$settings->labels_fontsize}}pt; --}} {{--    padding-right: .0001in; --}} {{--    overflow: hidden !important; --}} {{--    display: inline; --}} {{--    word-wrap: break-word; --}} {{--    word-break: break-all; --}} {{-- } --}} {{-- div.barcode_container { --}} {{--    width: 100%; --}} {{--    display: inline; --}} {{--    overflow: hidden; --}} {{-- } --}} {{-- .next-padding { --}} {{--    margin: {{ $settings->labels_pmargin_top }}in {{ $settings->labels_pmargin_right }}in {{ $settings->labels_pmargin_bottom }}in {{ $settings->labels_pmargin_left }}in; --}} {{-- } --}} {{-- @media print { --}} {{--    .noprint { --}} {{--        display: none !important; --}} {{--    } --}} {{--    .next-padding { --}} {{--        margin: {{ $settings->labels_pmargin_top }}in {{ $settings->labels_pmargin_right }}in {{ $settings->labels_pmargin_bottom }}in {{ $settings->labels_pmargin_left }}in; --}} {{--        font-size: 0; --}} {{--    } --}} {{-- } --}} {{-- @media screen { --}} {{--    .label { --}} {{--        outline: .02in black solid; /* outline doesn't occupy space like border does */ --}} {{--    } --}} {{--    .noprint { --}} {{--        font-size: 13px; --}} {{--        padding-bottom: 15px; --}} {{--    } --}} {{-- } --}} {{-- @if ($snipeSettings->custom_css) --}} {{--    {!! $snipeSettings->show_custom_css() !!} --}} {{-- @endif --}} .card {
        /* box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); */
        border-color: #000;
        border-style: solid;
        max-width: 300px;
        margin: auto;
        text-align: center;
    }

    .title {
        color: rgb(0, 0, 0);
        font-size: 20px;
        font-weight: bold;
        text-align: center;
    }

    /* h4 {
        padding-top: 20px;
    } */

    button {
        border: none;
        outline: 0;
        display: inline-block;
        padding: 8px;
        color: white;
        background-color: #000;
        text-align: center;
        cursor: pointer;
        width: 100%;
        font-size: 18px;
    }

    a {
        text-decoration: none;
        font-size: 22px;
        color: black;
    }

    button:hover,
    a:hover {
        opacity: 0.7;
    }

    @media print {
        #print-button {
            display: none;
        }
    }
</style>

<body>

    @php $count = 0; @endphp

    @foreach ($assets as $asset)
        @if ($count % 5 == 0)
            <div class="page-break"></div> <!-- Add a page break every 5 QR codes -->
        @endif

        <div class="card" style="width: 18rem; margin-top: 30px;" data-asset-tag="{{ $asset->asset_tag }}">
            <div class="title">
                <h4> Oceanside Solutions LLC</h4>
            </div>
            <div class="qrCode">
                {!! DNS2D::getBarcodeHTML($asset->asset_tag, 'QRCODE', 8, 8) !!}
            </div>
            <div class="card-body">
                <p class="card-text" style="padding-bottom: 15px;">Asset Number: {{ $asset->asset_tag }}</p>
            </div>
            <div class="card-footer" style="margin-top: 35px;">
                <button class="btn-print" onclick="generatePDF(this)">Print this QR</button>
            </div>
        </div>
        @php $count++; @endphp
    @endforeach

    <!-- Script to generate PDF -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js "></script>
    <script src="{{ asset('js/html2canvas.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script>
        const { jsPDF } = jspdf;

        function generatePDF(button) {
            swal.fire({
                title: "Loading",
                text: "Generating PDF"
            });

            var assetTag = $(button).closest('.card').data('asset-tag');
            var $card = $(button).closest('.card');

            $card.find('.btn-print').remove(); // Remove the button from the card

            html2canvas($card.get(0), { scale: 2 }).then(canvas => {
                console.log('working');
                var imgData = canvas.toDataURL('image/png');
                var doc = new jsPDF('landscape');

                var imageWidth = 100;
                var imageHeight = (canvas.height * imageWidth) / canvas.width;

                var x = (doc.internal.pageSize.getWidth() - imageWidth) / 2;
                var y = (doc.internal.pageSize.getHeight() - imageHeight) / 2;

                doc.addImage(imgData, 'PNG', x, y, imageWidth, imageHeight);

                doc.setFillColor(245);

                const fileName = `QR Code Asset ${assetTag}.pdf`;
                doc.save(fileName);

                $card.find('.card-footer').append('<button class="btn-print" onclick="generatePDF(this)">Print this QR</button>');

                swal.fire({
                    title: "Success",
                    text: "PDF Downloaded"
                });
            }).catch(err => { console.log('errc', err) });
        }
    </script>
</body>
</html>
