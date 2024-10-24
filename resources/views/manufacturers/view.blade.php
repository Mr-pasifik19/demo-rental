@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $manufacturer->name }}
    {{ trans('general.manufacturer') }}
    @parent
@stop

@section('header_right')

    <a href="{{ route('manufacturers.index') }}" class="btn btn-primary text-right"
        style="margin-right: 10px;">{{ trans('general.back') }}</a>


    <div class="btn-group pull-right">
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a
                    href="{{ route('manufacturers.edit', $manufacturer->id) }}">{{ trans('admin/manufacturers/table.update') }}</a>
            </li>
            <li><a href="{{ route('manufacturers.create') }}">{{ trans('admin/manufacturers/table.create') }}</a></li>
        </ul>
    </div>
@stop

{{-- Page content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">

                        <a href="#assets" data-toggle="tab">

                            <span class="hidden-lg hidden-md">
                                <i class="fas fa-barcode fa-2x"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">
                                {{ trans('general.assets') }}
                                {!! $manufacturer->assets &&
                                $manufacturer->assets()->AssetsForShow()->count() > 0
                                    ? '<badge class="badge badge-secondary">' .
                                        number_format(
                                            $manufacturer->assets()->AssetsForShow()->count(),
                                        ) .
                                        '</badge>'
                                    : '' !!}
                            </span>

                        </a>

                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade in active" id="assets">

                        @include('partials.asset-bulk-actions')
                        <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-cookie-id-table="assetsListingTable" data-pagination="true"
                            data-id-table="assetsListingTable" data-toolbar="#assetsBulkEditToolbar"
                            data-bulk-button-id="#bulkAssetEditButton" data-bulk-form-id="#assetsBulkForm"
                            data-search="true" data-show-fullscreen="true" data-side-pagination="server"
                            data-show-columns="true" data-show-export="true" data-show-refresh="true" data-sort-order="asc"
                            id="assetsListingTable" class="table table-striped snipe-table"
                            data-url="{{ route('api.assets.index', ['manufacturer_id' => $manufacturer->id, 'itemtype' => 'assets']) }}"
                            data-export-options='{
                                        "fileName": "export-manufacturers-{{ str_slug($manufacturer->name) }}-assets-{{ date('Y-m-d') }}",
                                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                        }'>
                        </table>

                    </div> <!-- /.tab-pane assets -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'manufacturer' . $manufacturer->name . '-export',
        'search' => false,
    ])

@stop
