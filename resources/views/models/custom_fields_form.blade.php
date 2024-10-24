@if ($model && $model->fieldset)
    @foreach ($model->fieldset->fields as $field)
        @if (str_contains($field->db_column_name(), 'replacement_value') ||
                str_contains($field->db_column_name(), 'value_for_customs_purposes'))
            <div class="form-group {{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">
                <label for="{{ $field->db_column_name() }}" class="col-md-3 control-label">{{ $field->name }}</label>
                <div class="col-md-9">
                    <div class="input-group col-md-4" style="padding-left: 0px;">
                        <input class="form-control" type="text" name="{{ $field->db_column_name() }}"
                            aria-label="{{ $field->db_column_name() }}" id="{{ $field->db_column_name() }}"
                            value="{{ Request::old($field->db_column_name(), isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)) }}" />
                        <span class="input-group-addon currencyBadge">
                            @if (isset($currency_type))
                                {{ $currency_type }}
                            @else
                                {{ $snipeSettings->default_currency }}
                            @endif
                        </span>
                    </div>
                    <div class="col-md-9" style="padding-left: 0px;">
                        {!! $errors->first(
                            $field->db_column_name(),
                            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        :message</span>',
                        ) !!}
                    </div>
                </div>
            </div>
        @else
            <div class="form-group{{ $errors->has($field->db_column_name()) ? ' has-error' : '' }}">
                <label for="{{ $field->db_column_name() }}" class="col-md-3 control-label">{{ $field->name }}
                </label>
                <div class="col-md-7 {{ $field->pivot->required == '1' ? ' required' : '' }}">
                    @if ($field->element != 'text')
                        <!-- Listbox -->
                        @if ($field->element == 'listbox')
                            <select name="{{ $field->db_column_name() }}" class="form-control select2"
                                id="select_{{ $field->db_column_name() }}">
                                @foreach ($field->formatFieldValuesAsArray() as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ Request::old(
                                            $field->db_column_name(),
                                            isset($item)
                                                ? Helper::gracefulDecrypt($field, htmlspecialchars($item->{$field->db_column_name()}, ENT_QUOTES))
                                                : $field->defaultValue($model->id),
                                        ) == $value
                                            ? 'selected'
                                            : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif ($field->element == 'textarea')
                            <textarea class="col-md-6 form-control" id="{{ $field->db_column_name() }}" name="{{ $field->db_column_name() }}">{{ Request::old($field->db_column_name(), isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)) }}</textarea>
                        @elseif ($field->element == 'checkbox')
                            <!-- Checkboxes -->
                            @foreach ($field->formatFieldValuesAsArray() as $key => $value)
                                <div>
                                    <label class="form-control">
                                        <input type="checkbox" value="{{ $value }}"
                                            name="{{ $field->db_column_name() }}[]"
                                            {{ isset($item)
                                                ? (in_array($value, array_map('trim', explode(',', $item->{$field->db_column_name()})))
                                                    ? '
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            checked="checked"'
                                                    : '')
                                                : (Request::old($field->db_column_name()) != ''
                                                    ? ' checked="checked"'
                                                    : (in_array($key, array_map('trim', explode(',', $field->defaultValue($model->id))))
                                                        ? '
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            checked="checked"'
                                                        : '')) }}>
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        @elseif ($field->element == 'radio')
                            @foreach ($field->formatFieldValuesAsArray() as $value)
                                <div>
                                    <label class="form-control">
                                        <input type="radio" value="{{ $value }}"
                                            name="{{ $field->db_column_name() }}"
                                            {{ isset($item)
                                                ? ($item->{$field->db_column_name()} == $value
                                                    ? ' checked="checked"'
                                                    : '')
                                                : (Request::old($field->db_column_name()) != ''
                                                    ? ' checked="checked"'
                                                    : (in_array($value, explode(', ', $field->defaultValue($model->id)))
                                                        ? ' checked="checked"'
                                                        : '')) }}>
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    @else
                        <!-- Date field -->
                        @if ($field->format == 'DATE')
                            <div class="input-group col-md-5" style="padding-left: 0px;">
                                <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"
                                    data-autoclose="true" data-date-clear-btn="true">
                                    <input type="text" class="form-control"
                                        placeholder="{{ trans('general.select_date') }}"
                                        name="{{ $field->db_column_name() }}" id="{{ $field->db_column_name() }}"
                                        readonly
                                        value="{{ old($field->db_column_name(), isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)) }}"
                                        style="background-color:inherit">
                                    <span class="input-group-addon"><i class="fas fa-calendar"
                                            aria-hidden="true"></i></span>
                                </div>
                            </div>
                        @else
                            @if ($field->field_encrypted == '0' || Gate::allows('assets.view.encrypted_custom_fields'))
                                <input type="text"
                                    value="{{ Request::old($field->db_column_name(), isset($item) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $field->defaultValue($model->id)) }}"
                                    id="{{ $field->db_column_name() }}" class="form-control"
                                    name="{{ $field->db_column_name() }}"
                                    placeholder="Enter {{ strtolower($field->format) }} text {{ str_contains($field->db_column_name(), '_snipeit_replacement_value_13') }}">
                            @else
                                <input type="text"
                                    value="{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}"
                                    class="form-control disabled" disabled>
                            @endif
                        @endif
                    @endif

                    @if ($field->help_text != '')
                        <p class="help-block">{{ $field->help_text }}</p>
                    @endif

                    <?php
                    $errormessage = $errors->first($field->db_column_name());
                    if ($errormessage) {
                        $errormessage = preg_replace('/ snipeit /', '', $errormessage);
                        print '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> ' . $errormessage . '</span>';
                    }
                    ?>
                </div>
                @if ($field->element === 'listbox')
                    <div class="col-md-1 col-sm-1 text-left">

                        <button type="button" data-toggle="modal" data-target="#modal-list{{ $field->id }}"
                            class="btn btn-sm btn-primary">{{ trans('button.new') }}</button>

                        <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="modal" id="modal-list{{ $field->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Create {{ $field->name }}</h4>
                                    {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button> --}}
                                </div>
                                <div class="modal-body">
                                    <form action="#" onsubmit="return false;">
                                        <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                                        </div>
                                        <div class="form-group row">
                                            <label for="fname"
                                                class="control-label col-sm-2">{{ trans('general.name') }}</label>
                                            <div class="col-sm-10">
                                                <input type='text' name="new_field" id='new_field'
                                                    class="form-control">
                                                <input type="hidden" name="category_type" id="category_type"
                                                    value="asset">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                                    <button type="button" class="btn btn-primary" id="save-field">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($field->field_encrypted)
                    <div class="col-md-1 col-sm-1 text-left">
                        <i class="fas fa-lock" data-tooltip="true" data-placement="top"
                            title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
                    </div>
                @endif


            </div>
        @endif
    @endforeach
@endif
@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        @if ($model && $model->fieldset)
            @foreach ($model->fieldset->fields as $field)
                @if ($field->element === 'listbox')
                    $(function() {
                        $('#modal-list{{ $field->id }}').on('show.bs.modal', function() {
                            var modal = $(this);
                            var name = modal.find('.modal-body #new_field');
                            modal.find("#modal-list{{ $field->id }}").off("click");

                            function handleSaveField() {
                                if (name.val() === "") {
                                    alert('Fill this form!');
                                } else {
                                    $.ajax({
                                        type: 'GET',
                                        url: '{{ route('api.fields.saveField') }}',
                                        dataType: 'JSON',
                                        headers: {
                                            "X-Requested-With": "XMLHttpRequest",
                                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                                "content"),
                                        },
                                        data: {
                                            fields: name.val(), // Use name.val() instead of name
                                            idField: '{{ $field->id }}'
                                        },
                                        success: function(response) {
                                            name.val("");
                                            modal.modal("hide");
                                            var s = response.payload;

                                            var formSelect = $(
                                                '#select_{{ $field->db_column_name() }}');
                                            formSelect.append('<option value="' + s +
                                                '" selected="selected">' + s + "</option>");
                                            formSelect.trigger('change');
                                            // console.log(s);

                                        },
                                        error: function(response) {
                                            // console.log(response.messages);
                                            name.val("");
                                            modal.modal("hide");

                                        },
                                    });
                                }
                            }

                            modal.find('#save-field').off('click').on('click', handleSaveField);
                        });
                    });
                @endif
            @endforeach
        @endif
    </script>
@endsection

<script nonce="{{ csrf_token() }}">
    @if ($model && $model->fieldset)
        @foreach ($model->fieldset->fields as $field)
            @if ($field->element === 'listbox')
                $(function() {
                    $('#modal-list{{ $field->id }}').on('show.bs.modal', function() {
                        var modal = $(this);
                        var name = modal.find('.modal-body #new_field');
                        modal.find("#modal-list{{ $field->id }}").off("click");

                        function handleSaveField() {
                            if (name.val() === "") {
                                alert('Fill this form!');
                            } else {
                                $.ajax({
                                    type: 'GET',
                                    url: '{{ route('api.fields.saveField') }}',
                                    dataType: 'JSON',
                                    headers: {
                                        "X-Requested-With": "XMLHttpRequest",
                                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                                    },
                                    data: {
                                        fields: name.val(), // Use name.val() instead of name
                                        idField: '{{ $field->id }}'
                                    },
                                    success: function(response) {
                                        name.val("");
                                        modal.modal("hide");
                                        var s = response.payload;

                                        var formSelect = $('#select_{{ $field->db_column_name() }}');
                                        formSelect.append('<option value="' + s +
                                            '" selected="selected">' + s + "</option>");
                                        formSelect.trigger('change');
                                        // console.log(s);

                                    },
                                    error: function(response) {
                                        // console.log(response.messages);
                                        name.val("");
                                        modal.modal("hide");

                                    },
                                });
                            }
                        }

                        modal.find('#save-field').off('click').on('click', handleSaveField);
                    });
                });
            @endif
        @endforeach
    @endif
</script>
