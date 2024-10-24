{{-- See snipeit_modals.js for what powers this --}}
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h2 class="modal-title">{{ trans('admin/models/table.create') }}</h2>
        </div>
        <div class="modal-body">
            <form action="{{ route('api.models.store') }}" onsubmit="return false">
                <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                </div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-name">{{ trans('general.name') }}:
                        </label></div>
                    <div class="col-md-8 col-xs-12 required"><input type='text' name="name" id='modal-name'
                            class="form-control"></div>
                </div>

                {{-- <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-category_id">{{ trans('general.category') }}:</label></div>
                    <div class="col-md-8 col-xs-12 required">
                        <select class="js-data-ajax" data-endpoint="categories/asset" name="category_id"
                            style="width: 100%" id="modal-category_id"></select>
                    </div>
                </div> --}}
                {{-- Ganti disini --}}
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label
                            for="modal-modelno">{{ trans('admin/categories/general.category_name') }}:</label>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <select name="category_id" id="category_id" required class="form-control select2">
                            <option value="">Select Category</option>
                            @foreach (\App\Models\Category::all() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <button type="button" class="btn btn-sm btn-primary">{{ trans('button.new') }}</button>

                    </div>
                </div>
                {{-- @include ('partials.forms.edit.category-select', [
                    'translated_name' => trans('admin/categories/general.category_name'),
                    'fieldname' => 'category_id',
                    'required' => 'true',
                    'category_type' => 'asset',
                ]) --}}
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label
                            for="modal-modelno">{{ trans('general.manufacturer') }}:</label>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <select name="manufacturer_id" id="manufacturer_id" required class="form-control select2">
                            <option value="">Select Manufacturer</option>
                            @foreach (\App\Models\Manufacturer::all() as $manufactur)
                                <option value="{{ $manufactur->id }}">{{ $manufactur->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <button type="button" id="testModal"
                            class="btn btn-sm btn-primary">{{ trans('button.new') }}</button>
                    </div>
                </div>
                {{-- @include ('partials.forms.edit.category-select', [
                        'translated_name' => trans('admin/categories/general.category_name'),
                        'fieldname' => 'category_id',
                        'required' => 'true',
                        'category_type' => 'asset',
                    ]) --}}
                {{-- <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-manufacturer_id">{{ trans('general.manufacturer') }}:
                        </label></div>
                    <div class="col-md-8 col-xs-12">
                        <select class="js-data-ajax" data-endpoint="manufacturers" name="manufacturer_id"
                            style="width: 100%" id="modal-manufacturer_id"></select>
                    </div>
                </div> --}}

                {{-- <div class="dynamic-form-row">
                    @include ('partials.forms.edit.manufacturer-select', [
                        'translated_name' => trans('general.manufacturer'),
                        'fieldname' => 'manufacturer_id',
                    ])
                </div> --}}

                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-modelno">{{ trans('general.model_no') }}:</label>
                    </div>
                    <div class="col-md-8 col-xs-12"><input type='text' name="model_number" id='modal-model_number'
                            class="form-control"></div>
                </div>

                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label
                            for="modal-fieldset_id">{{ trans('admin/models/general.fieldset') }}:</label></div>
                    <div class="col-md-8 col-xs-12">
                        {{ Form::select('fieldset_id', Helper::customFieldsetList(), Request::old('fieldset_id'), ['class' => 'select2', 'id' => 'modal-fieldset_id', 'style' => 'width:350px']) }}
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
            <button type="button" class="btn btn-primary" id="modal-save">{{ trans('general.save') }}</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
