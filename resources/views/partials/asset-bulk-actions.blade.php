<div id="{{ (isset($id_divname)) ? $id_divname : 'assetsBulkEditToolbar' }}" style="min-width:400px">
    {{ Form::open([
          'method' => 'POST',
          'route' => ['hardware/bulkedit'],
          'class' => 'form-inline',
          'id' => (isset($id_formname)) ? $id_formname : 'assetsBulkForm',
    ])}}

    <div class="d-flex align-items-center mb-3">
        <label for="bulk_actions" class="font-weight-bold fs-1" style="margin-right: 5px;">
            ACTIONS BULK
            {{-- <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> --}}
         </label>

        <select name="bulk_actions" class="form-control select2" id="action_bulk" aria-label="bulk_actions" style="min-width: 350px;">
            @if((isset($status)) && ($status == 'Deleted'))
                @can('delete', \App\Models\Asset::class)
                    <option value="restore">{{trans('button.restore')}}</option>
                @endcan
            @else
                @can('update', \App\Models\Asset::class)
                    <option value="edit">{{ trans('button.edit') }}</option>
                @endcan
                {{-- @can('delete', \App\Models\Asset::class)
                    <option value="delete">{{ trans('button.delete') }}</option>
                @endcan --}}
                <option value="labels" accesskey="l">{{ trans_choice('button.generate_labels', 2) }}</option>
            @endif
        </select>

        <button class="btn btn-primary" id="{{ (isset($id_button)) ? $id_button : 'bulkAssetEditButton' }}" disabled>{{ trans('button.go') }}</button>
    </div>

    {{ Form::close() }}
</div>
