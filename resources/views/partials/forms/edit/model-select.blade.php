<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    {{-- {{ Form::label($fieldname, $translated_name, ['class' => 'col-md-3 control-label']) }} --}}
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{!! $translated_name !!}</label>

    <div class="col-md-7{{ isset($field_req) || (isset($required) && $required == 'true') ? ' required' : '' }}">
        <select class="js-data-ajax" data-endpoint="models" data-placeholder="{{ trans('general.select_model') }}"
            name="{{ $fieldname }}" style="width: 100%" id="model_select_id"
            aria-label="{{ $fieldname }}"{!! isset($field_req) ? ' data-validation="required" required' : '' !!}{{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}>
            @if ($model_id = old($fieldname, $item->{$fieldname} ?? (request($fieldname) ?? '')))
                <option value="{{ $model_id }}" selected="selected">
                    {{ \App\Models\AssetModel::find($model_id) ? \App\Models\AssetModel::find($model_id)->name : '' }}
                </option>
            @else
                <option value="" role="option">{{ trans('general.select_model') }}</option>
            @endif

        </select>
    </div>
    <div class="col-md-1 col-sm-1 text-left">
        {{-- @if (Request::is('hardware*')) --}}
        @can('create', \App\Models\AssetModel::class)
            @if (!isset($hide_new) || $hide_new != 'true')
                <a href='#' data-toggle="modal" data-target="#modal-create-model" data-select='model_select_id'
                    class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
                <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                </span>
            @endif
        @endcan
        {{-- @else
            @can('create', \App\Models\AssetModel::class)
                @if (!isset($hide_new) || $hide_new != 'true')
                    <a href='{{ route('modal.show', 'model') }}' data-toggle="modal" data-target="#createModal"
                        data-select='model_select_id' class="btn  btn-primary">{{ trans('button.new') }}</a>
                    <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                        <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                    </span>
                @endif
            @endcan
        @endif --}}
    </div>
    {{-- mesti ganti nnti --}}
    {!! $errors->first(
        $fieldname,
        '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>',
    ) !!}
</div>


{{-- Modal --}}

<div class="modal" id="modal-create-model" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ trans('admin/models/table.create') }}</h2>
            </div>
            <div class="modal-body">
                <form action="#" onsubmit="return false" id="formAssetModel">
                    <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                    </div>
                    <div class="form-group row">
                        <label for="fname" class="control-label col-sm-2">{{ trans('general.name') }}</label>
                        <div class="col-sm-10">
                            <input type='text' name="name" id='name' class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="category_id"
                            class="control-label col-sm-2">{{ trans('admin/categories/general.category_name') }}</label>
                        <div class="col-sm-8">
                            <select class="js-data-ajax" data-endpoint="categories/asset"
                                data-placeholder="{{ trans('general.select_category') }}" name="category_id"
                                style="width: 100%" id="category_id" aria-label="category_id">
                                @if ($category_id = old('category_id', isset($item) ? $item->{'category_id'} : ''))
                                    <option value="{{ $category_id }}" selected="selected" role="option"
                                        aria-selected="true" role="option">
                                        {{ \App\Models\Category::find($category_id) ? \App\Models\Category::find($category_id)->name : '' }}
                                    </option>
                                @else
                                    <option value="" role="option">{{ trans('general.select_category') }}
                                    </option>
                                @endif

                            </select>
                        </div>
                        <div class="col-sm-2">
                            @can('create', \App\Models\Category::class)
                                <a href="#" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal-create-category">
                                    {{ trans('button.new') }}
                                </a>
                            @endcan

                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="manufacturer_id"
                            class="control-label col-sm-2">{{ trans('general.manufacturer') }}</label>
                        <div class="col-sm-8">
                            <select class="js-data-ajax" data-endpoint="manufacturers"
                                data-placeholder="{{ trans('general.select_manufacturer') }}" name="manufacturer_id"
                                style="width: 100%" id="manufacturer_id" aria-label="manufacturer_id">
                                @if ($manufacturer_id = old('manufacturer_id', isset($item) ? $item->{'manufacturer_id'} : ''))
                                    <option value="{{ $manufacturer_id }}" selected="selected" role="option"
                                        aria-selected="true" role="option">
                                        {{ \App\Models\Manufacturer::find($manufacturer_id) ? \App\Models\Manufacturer::find($manufacturer_id)->name : '' }}
                                    </option>
                                @else
                                    <option value="" role="option">{{ trans('general.select_manufacturer') }}
                                    </option>
                                @endif

                            </select>
                        </div>
                        <div class="col-sm-2">
                            <a href="#" class="btn btn-primary" data-toggle="modal"
                                data-target="#modal-create-manufacturer">
                                {{ trans('button.new') }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="modal-model_number"
                            class="control-label col-sm-2">{{ trans('general.model_no') }}</label>
                        <div class="col-sm-10">
                            <input type='text' name="model_number" id='modal-model_number' class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="fieldset_id"
                            class="control-label col-sm-2">{{ trans('admin/models/general.fieldset') }}</label>
                        <div class="col-sm-10">
                            {{ Form::select('fieldset_id', Helper::customFieldsetList(), Request::old('fieldset_id'), ['class' => 'form-control select2', 'id' => 'modal-fieldset_id', 'style' => 'width:350px']) }}
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                <button type="button" class="btn btn-primary" id="modal-save-model"
                    data-model='{{ route('api.models.store') }}'>Save</button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="modal-create-category">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Categories</h4>
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
                            <input type="hidden" name="category_type" id="category_type" value="asset">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>
                <button type="button" class="btn btn-primary" id="save-category"
                    data-category='{{ route('api.categories.store') }}'>Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modal-create-manufacturer">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Manufacturers</h4>
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
                <button type="button" class="btn btn-primary" id="save-manufacturer"
                    data-manufacturer="{{ route('api.manufacturers.store') }}">Save</button>
            </div>
        </div>
    </div>
</div>
