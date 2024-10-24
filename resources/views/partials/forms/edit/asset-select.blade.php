<!-- Asset -->
<div id="assigned_asset" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}" {!! (isset($style))
    ? ' style="' .e($style).'"' : '' !!}>
    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-7{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select class="js-data-ajax select2" data-endpoint="hardware"
            data-placeholder="{{ trans('general.select_asset') }}" aria-label="{{ $fieldname }}" name="{{ $fieldname }}"
            style="width: 100%" id="{{ (isset($select_id)) ? $select_id : 'assigned_asset_select' }}" {{
            (isset($multiple)) ? ' multiple' : '' }}{!! (!empty($asset_status_type)) ? ' data-asset-status-type="' .
            $asset_status_type . '"' : '' !!}{!! (isset($field_req) ? ' data-validation="required" required' : '' ) !!}>
            @if ((!isset($unselect)) && ($asset_id = old($fieldname, (isset($asset) ? $asset->id : (isset($item) ?
            $item->{$fieldname} : '')))))
            <option value="{{ $asset_id }}" selected="selected" role="option" aria-selected="true" role="option">
                {{ (\App\Models\Asset::find($asset_id)) ? \App\Models\Asset::find($asset_id)->present()->fullName : ''
                }}
            </option>
            @else
            @if(!isset($multiple))
            <option value="" role="option">{{ trans('general.select_asset') }}</option>
            @endif
            @endif
        </select>
        <br>
        <!-- Container for the scanner -->
        <div id="reader" class="col-md-8" style="width:100%"></div>

    </div>{!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"
            aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>






<!-- Include jQuery library -->
<script src="{{ asset('js/jquery.js') }}"></script>

<!-- Include html5-qrcode library -->
<script src="{{ asset('js/scan.js') }}"></script>

@push('js')

<script nonce="{{ csrf_token() }}">
    const html5QrcodeScanner = new Html5QrcodeScanner("reader", {
        fps: 3,
        qrbox: { width: 250, height: 250 }
    });

    function isAssetIdAssigned(assetId) {
        const assignedAssetsSelect = document.getElementById('assigned_assets_select');

        if (assignedAssetsSelect) {
            const assignedOptions = assignedAssetsSelect.getElementsByTagName('option');
            for (const option of assignedOptions) {
                if (option.value === assetId) {
                    return true;
                }
            }
        }
        return false;
    }

    function addAssetToAssignedAssets(assetId, assetName) {
        const assignedAssetsSelect = document.getElementById('assigned_assets_select');

        if (assignedAssetsSelect) {
            if (!isAssetIdAssigned(assetId)) {
                const existingOption = assignedAssetsSelect.querySelector(`option[value="${assetId}"]`);
                if (existingOption) {
                    existingOption.remove();
                }

                const option = document.createElement('option');
                option.value = assetId;
                option.text = assetName;
                assignedAssetsSelect.add(option);
                option.selected = true;
            } else {
                // console.log(`Asset dengan ID ${assetId} sudah ditetapkan sebelumnya.`);
            }
        } else {
            // console.error("Dropdown element not found.");
        }
    }

    function onScanSuccess(qrCodeMessage) {
        console.log("QR Code detected and processed: " + qrCodeMessage);

        const baseUrl = "{{url('/')}}";
        const endpoint = "hardware";
        const assetStatusType = "RTD";

        $.ajax({
            url: `${baseUrl}/api/v1/${endpoint}/selectlist?search=${qrCodeMessage}&page=1${assetStatusType ? '&assetStatusType=' + assetStatusType : ''}`,
            dataType: 'json',
            headers: {
                "X-Requested-With": 'XMLHttpRequest',
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const result = response.results[0];
                const assetName = result.text;
                const assetId = result.id;
                if (!isAssetIdAssigned(assetId)) {
                    addAssetToAssignedAssets(assetId, assetName);
                } else {
                    // console.log('id sudah ada');
                }
            },
            error: function (error) {
                // console.error('Error fetching asset details:', error);
            }
        });
    }

    html5QrcodeScanner.render(onScanSuccess);
</script>


@endpush