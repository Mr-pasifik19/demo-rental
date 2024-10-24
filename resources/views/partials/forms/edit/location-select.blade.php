<!-- Location -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"
    {!! isset($style) ? ' style="' . e($style) . '"' : '' !!}>

    {{-- {{ Form::label($fieldname, $translated_name, ['class' => 'col-md-3 control-label']) }} --}}
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{!! $translated_name !!}</label>

    <div class="col-md-7{{ isset($required) && $required == 'true' ? ' required' : '' }}">
        <select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}"
            name="{{ $fieldname }}" style="width: 100%" id="{{ $fieldname }}_location_select"
            aria-label="{{ $fieldname }}"
            {!! isset($item) && Helper::checkIfRequired($item, $fieldname) ? ' data-validation="required" required' : '' !!}{{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}
            {!! isset($field_req) ? ' data-validation="required" required' : '' !!}>
            @if ($location_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
                <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true"
                    role="option">
                    {{ \App\Models\Location::find($location_id) ? \App\Models\Location::find($location_id)->name : '' }}
                </option>
            @else
                <option value="" role="option">{{ trans('general.select_location') }}</option>
            @endif
        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Location::class)
            @if (!isset($hide_new) || $hide_new != 'true')
                <a href='{{ route('modal.show', 'location') }}' data-toggle="modal" data-target="#createModal"
                    data-select='{{ $fieldname }}_location_select'
                    class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>

    {!! $errors->first(
        $fieldname,
        '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times"
                            aria-hidden="true"></i> :message</span></div>',
    ) !!}

    @if (isset($help_text))
        <div class="col-md-7 col-sm-11 col-md-offset-3">
            <p class="help-block">{{ $help_text }}</p>
        </div>
    @endif


</div>

@push('js')
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function() {

            // ketika semua element ready
            var fieldName = '{{ $fieldname }}';
            var id = '#' + fieldName + '_location_select';
            var locationSelect = document.querySelector(id);
            var Idawal = locationSelect.value;

            if (Idawal) {
                $.ajax({
                    type: 'GET',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('api.locations.show', ['location' => '__id__']) }}'.replace('__id__',
                        Idawal),
                    success: function(data) {
                        var currency = document.getElementsByClassName('currencyBadge');
                        if (data['currency'] != null) {
                            for (var i = 0; i < currency.length; i++) {
                                currency[i].innerHTML = data['currency'];
                            }
                        } else {
                            for (var i = 0; i < currency.length; i++) {
                                currency[i].innerHTML = "-";
                            }
                        }


                    },
                    error: function() {
                        console.error('Failed to fetch location details');
                    }
                });
            }




            // Ketika nilai lokasi berubah
            $('#{{ $fieldname }}_location_select').on('change', function() {
                // Mendapatkan nilai ID lokasi yang terpilih
                var selectedLocationId = $(this).val();

                // Mengecek apakah nilai ID lokasi tidak kosong
                if (selectedLocationId) {
                    // Ajax request to fetch location details including currency
                    $.ajax({
                        type: 'GET',
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('api.locations.show', ['location' => '__id__']) }}'.replace(
                            '__id__', selectedLocationId),
                        success: function(data) {
                            var myElements = document.getElementsByClassName(
                                'currencyBadge');
                            if (data['currency'] !== null) {
                                for (var i = 0; i < myElements.length; i++) {
                                    myElements[i].innerHTML = data['currency'];
                                }
                            } else {
                                for (var i = 0; i < myElements.length; i++) {
                                    myElements[i].innerHTML = "-";
                                }
                            }

                        },
                        error: function() {
                            console.error('Failed to fetch location details');
                        }
                    });
                }
            });
        });
    </script>
@endpush
