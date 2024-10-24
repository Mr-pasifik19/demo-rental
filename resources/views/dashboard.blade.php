@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('general.dashboard') }}
    @parent
@stop

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
{{-- Page content --}}
@section('content')

    @if ($snipeSettings->dashboard_message != '')
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                {!! Helper::parseEscapedMarkedown($snipeSettings->dashboard_message) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- panel -->
        <div class="col-lg-3 col-xs-6">
            <a href="{{ route('hardware.index') }}">
                <!-- small box -->
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3>{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</h3>
                        <p>{{ strtolower(trans('general.assets')) }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <i class="fas fa-barcode" aria-hidden="true"></i>
                    </div>
                    @can('index', \App\Models\Asset::class)
                        <a href="{{ route('hardware.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                                class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                    @endcan
                </div>
            </a>
        </div><!-- ./col -->





        <!-- ini aksesoris dibagian dashboard, tpi bkn navbar -->
        {{-- <div class="col-lg-3 col-xs-6"> --}}
        <!-- small box -->
        {{-- <a href="{{ route('accessories.index') }}">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3> {{ number_format($counts['accessory']) }}</h3>
                    <p>{{ strtolower(trans('general.accessories')) }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <i class="far fa-keyboard"></i>
                </div>
                @can('index', \App\Models\Accessory::class)
                <a href="{{ route('accessories.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                        class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                @endcan
            </div>
        </a>
    </div> --}}



        {{-- <div class="col-lg-3 col-xs-6">
        <!-- small box -->

        <a href="{{ route('consumables.index') }}">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3> {{ number_format($counts['consumable']) }}</h3>
                    <p>{{ strtolower(trans('general.consumables')) }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <i class="fas fa-tint"></i>
                </div>
                @can('index', \App\Models\Consumable::class)
                <a href="{{ route('consumables.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                        class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                @endcan
            </div>
    </div><!-- ./col --> --}}


        {{-- <div class="col-lg-3 col-xs-6">
        <a href="{{ route('components.index') }}">

            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ number_format($counts['component']) }}</h3>
                    <p>{{ strtolower(trans('general.components')) }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <i class="far fa-hdd"></i>
                </div>
                @can('view', \App\Models\License::class)
                <a href="{{ route('components.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                        class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                @endcan
            </div>
        </a>
    </div><!-- ./col --> --}}

        <div class="col-lg-3 col-xs-6">
            <a href="{{ route('users.index') }}">
                <!-- small box -->
                <div class="small-box bg-light-blue">
                    <div class="inner">
                        <h3>{{ number_format($counts['user']) }}</h3>
                        <p>{{ strtolower(trans('general.people')) }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <i class="fas fa-users"></i>
                    </div>
                    @can('view', \App\Models\License::class)
                        <a href="{{ route('users.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                                class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                    @endcan
                </div>
            </a>
        </div><!-- ./col -->


        {{-- ini adalah linces ganti jadi movement --}}
        <div class="col-lg-3 col-xs-6">
            <a href="{{ route('project-movement.index') }}">

                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ number_format($counts['project']) }}</h3>
                        <p>{{ strtolower(trans('general.project')) }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <i class="fas fa-book"></i>
                    </div>
                    @can('view', \App\Models\ProjectMovement::class)
                        <a href="{{ route('project-movement.index') }}"
                            class="small-box-footer">{{ trans('general.view_all') }}
                            <i class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                    @endcan
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-xs-6">
            <a href="{{ route('movement.index') }}">

                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ number_format($counts['movement']) }}</h3>
                        <p>{{ strtolower(trans('general.movement')) }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <i class="far fa-save"></i>
                    </div>
                    @can('view', \App\Models\MovementsModel::class)
                        <a href="{{ route('movement.index') }}" class="small-box-footer">{{ trans('general.view_all') }} <i
                                class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
                    @endcan
                </div>
            </a>
        </div>


    </div>

    @if ($counts['grand_total'] == 0)
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('general.dashboard_info') }}</h2>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="progress">
                                    <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                        <span class="sr-only">{{ trans('general.60_percent_warning') }}</span>
                                    </div>
                                </div>


                                <p><strong>{{ trans('general.dashboard_empty') }}</strong></p>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                @can('create', \App\Models\Asset::class)
                                    <a class="btn bg-teal" style="width: 100%"
                                        href="{{ route('hardware.create') }}">{{ trans('general.new_asset') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-3">
                                @can('create', \App\Models\Asset::class)
                                    <a class="btn bg-light-blue" style="width: 100%"
                                        href="{{ route('project-movement.create') }}">New Project</a>
                                @endcan
                            </div>
                            {{-- <div class="col-md-3">
                        @can('create', \App\Models\License::class)
                        <a class="btn bg-maroon" style="width: 100%" href="{{ route('licenses.create') }}">{{
                            trans('general.new_license') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-3">
                        @can('create', \App\Models\Accessory::class)
                        <a class="btn bg-orange" style="width: 100%" href="{{ route('accessories.create') }}">{{
                            trans('general.new_accessory') }}</a>
                        @endcan
                    </div> --}}
                            {{-- <div class="col-md-3">
                        @can('create', \App\Models\Consumable::class)
                        <a class="btn bg-purple" style="width: 100%" href="{{ route('consumables.create') }}">{{
                            trans('general.new_consumable') }}</a>
                        @endcan
                    </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- recent activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('general.recent_activity') }}</h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table data-cookie-id-table="dashActivityReport" data-height="350"
                                        data-pagination="false" data-id-table="dashActivityReport"
                                        data-side-pagination="server" data-sort-order="desc" data-sort-name="created_at"
                                        id="dashActivityReport" class="table table-striped snipe-table"
                                        data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                                        <thead>
                                            <tr>
                                                <th data-field="icon" data-visible="true" style="width: 40px;"
                                                    class="hidden-xs" data-formatter="iconFormatter"><span
                                                        class="sr-only">{{ trans('admin/hardware/table.icon') }}</span>
                                                </th>
                                                <th class="col-sm-3" data-visible="true" data-field="created_at"
                                                    data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                                <th class="col-sm-2" data-visible="true" data-field="admin"
                                                    data-formatter="usersLinkObjFormatter">{{ trans('general.admin') }}
                                                </th>
                                                <th class="col-sm-2" data-visible="true" data-field="action_type">
                                                    {{ trans('general.action') }}</th>
                                                <th class="col-sm-3" data-visible="true" data-field="item"
                                                    data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}
                                                </th>
                                                <th class="col-sm-2" data-visible="true" data-field="target"
                                                    data-formatter="polymorphicItemFormatter">
                                                    {{ trans('general.target') }}</th>
                                            </tr>
                                        </thead>
                                    </table>

                                </div><!-- /.responsive -->
                            </div><!-- /.col -->
                            <div class="text-center col-md-12" style="padding-top: 10px;">
                                <a href="{{ route('reports.activity') }}" class="btn btn-primary btn-sm"
                                    style="width: 100%">{{ trans('general.viewall') }}</a>
                            </div>
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            {{ \App\Models\Setting::getSettings()->dash_chart_type == 'name'
                                ? trans('general.assets_by_status')
                                : trans('general.assets_by_status_type') }}
                        </h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="chart-responsive">
                                    <canvas id="statusPieChart" height="230"></canvas>
                                </div> <!-- ./chart-responsive -->
                            </div> <!-- /.col -->
                        </div> <!-- /.row -->
                    </div><!-- /.box-body -->
                </div> <!-- /.box -->
            </div>

        </div>
        <!--/row-->
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <!-- 3rd Party-->
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h2 class="box-title">3rd Party {{ trans('general.asset') }}</h2>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fas fa-minus" aria-hidden="true"></i>
                                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="tableThirdParty" class="display" style="width:100%">
                                            <thead>

                                                <div id="filter_status" style="text-align: right;  margin-bottom: 5px;">
                                                    <label style="margin-right: 10px;">Filter by status:</label>
                                                    <select id="statusFilter" style="height: 35px; width: 180px">
                                                        <option value="">All</option>
                                                        <option value="Booked">Booked</option>
                                                        <option value="In Project">In Project</option>
                                                        <option value="Available">Available</option>
                                                        <option value="Archived">Archived</option>
                                                        <option value="Out for Calibration">Out for Calibration</option>
                                                        <option value="Damaged">Damaged</option>
                                                    </select>
                                                </div>



                                                <tr>
                                                    <th class="col-sm-3" data-visible="true" data-field="name"
                                                        data-sortable="true" style="width: 10%;">
                                                        {{ trans('general.asset_number') }}
                                                    </th>
                                                    <th class="col-sm-3" data-visible="true" data-field="name"
                                                        data-sortable="true" style="width: 40%;">
                                                        {{ trans('general.name') }}
                                                    </th>

                                                    <th class="col-sm-3" data-visible="true" data-field="location"
                                                        data-sortable="true" style="width: 40%;">
                                                        {{ trans('general.location') }}
                                                    </th>
                                                    <th class="col-sm-3" data-visible="true" data-field="status"
                                                        data-sortable="true" style="width: 10%;">
                                                        {{ trans('general.status') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assets as $index => $asset)
                                                    <tr>
                                                        <td><a
                                                                href="{{ route('hardware.show', $asset->id) }}">{{ $asset->asset_tag }}</a>
                                                        </td>
                                                        <td>{{ $asset->name }}</td>

                                                        <td>{{ $asset->location->name }}</td>
                                                        <td>
                                                            @if (str_contains($asset->assetStatus->name, 'Booked'))
                                                                <span
                                                                    class="text-orange text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @elseif (str_contains($asset->assetStatus->name, 'In Project'))
                                                                <span
                                                                    class="text-green text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @elseif (str_contains($asset->assetStatus->name, 'Available'))
                                                                <span
                                                                    class="text-blue text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @elseif (str_contains($asset->assetStatus->name, 'Archived'))
                                                                <span
                                                                    class="text-orange text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @elseif (str_contains($asset->assetStatus->name, 'Out for Calibration'))
                                                                <span
                                                                    class="text-red text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @elseif (str_contains($asset->assetStatus->name, 'Damaged'))
                                                                <span
                                                                    class="text-red text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @else
                                                                <span
                                                                    class="text-orange text-bold">{{ $asset->assetStatus->name }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div> <!-- /.col -->

                            </div> <!-- /.row -->

                        </div><!-- /.box-body -->
                    </div> <!-- /.box -->
                </div>
                <div class="col-md-6">
                    <!-- Alarm -->
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h2 class="box-title">Alarm</h2>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fas fa-minus" aria-hidden="true"></i>
                                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class="bootstrap-table bootstrap3">
                                            <div style="text-align: right; margin-bottom: 5px;">
                                                <a href='#' data-toggle="modal" data-target="#modal-create-alarm"
                                                    class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
                                            </div>
                                            <table id="tableAlarm" class="display" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%;">Task</th>
                                                        <th style="width: 20%;">Due Date</th>
                                                        <th style="width: 40%;" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($alarms as $alarm)
                                                        <tr>
                                                            <td>
                                                                {{ $alarm->task }}
                                                                @if ($alarm->status)
                                                                    </br>
                                                                    <span class="badge"
                                                                        style="background-color:#198754">Done</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $alarm->due_date }}</td>
                                                            <td>
                                                                @if ($alarm->status)
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-check form-check-inline">
                                                                                <input type="hidden">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <button
                                                                                class="btn btn-sm btn-danger delete-alarm-btn"
                                                                                data-toggle="modal"
                                                                                data-target="#deleteAlarmModal"
                                                                                data-id="{{ $alarm->id }}">Delete</button>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-check form-check-inline">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    id="checkAlarm{{ $alarm->id }}"
                                                                                    data-id="{{ $alarm->id }}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <button
                                                                                class="btn btn-sm btn-danger delete-alarm-btn"
                                                                                data-toggle="modal"
                                                                                data-target="#deleteAlarmModal"
                                                                                data-id="{{ $alarm->id }}">Delete</button>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> <!-- /.col -->
                            </div> <!-- /.row -->
                        </div><!-- /.box-body -->
                    </div> <!-- /.box -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Locations -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('general.asset') }} {{ trans('general.locations') }}</h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table data-cookie-id-table="dashLocationSummary" data-height="400"
                                        data-pagination="true" data-side-pagination="server" data-sort-order="desc"
                                        data-sort-field="assets_count" id="dashLocationSummary"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

                                        <thead>
                                            <tr>
                                                <th class="col-sm-3" data-visible="true" data-field="name"
                                                    data-formatter="locationsLinkFormatter" data-sortable="true">
                                                    {{ trans('general.name') }}</th>

                                                <th class="col-sm-1" data-visible="true" data-field="assets_count"
                                                    data-sortable="true">
                                                    <i class="fas fa-barcode" aria-hidden="true"></i>
                                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                                </th>
                                                {{-- <th class="col-sm-1" data-visible="true" data-field="assigned_assets_count"
                                            data-sortable="true">

                                            {{ trans('general.assigned') }}
                                        </th>
                                        <th class="col-sm-1" data-visible="true" data-field="users_count"
                                            data-sortable="true">
                                            <i class="fas fa-users" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.people') }}</span>

                                        </th> --}}

                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div> <!-- /.col -->
                            <div class="text-center col-md-12" style="padding-top: 10px;">
                                <a href="{{ route('locations.index') }}" class="btn btn-primary btn-sm"
                                    style="width: 100%">{{ trans('general.viewall') }}</a>
                            </div>
                        </div> <!-- /.row -->

                    </div><!-- /.box-body -->
                </div> <!-- /.box -->
            </div>


            <div class="col-md-6">
                <!-- Categories -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('general.asset') }} {{ trans('general.categories') }}</h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table data-cookie-id-table="dashCategorySummary" data-height="400"
                                        data-pagination="true" data-side-pagination="server" data-sort-order="desc"
                                        data-sort-field="assets_count" id="dashCategorySummary"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

                                        <thead>
                                            <tr>
                                                <th class="col-sm-3" data-visible="true" data-field="name"
                                                    data-formatter="categoriesLinkFormatter" data-sortable="true">
                                                    {{ trans('general.name') }}</th>
                                                <th class="col-sm-3" data-visible="true" data-field="category_type"
                                                    data-sortable="true">
                                                    {{ trans('general.type') }}
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="assets_count"
                                                    data-sortable="true">
                                                    <i class="fas fa-barcode" aria-hidden="true"></i>
                                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                                </th>
                                                {{-- <th class="col-sm-1" data-visible="true" data-field="accessories_count"
                                            data-sortable="true">
                                            <i class="far fa-keyboard" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                        </th> --}}
                                                {{-- <th class="col-sm-1" data-visible="true" data-field="consumables_count"
                                            data-sortable="true">
                                            <i class="fas fa-tint" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                        </th> --}}
                                                {{-- <th class="col-sm-1" data-visible="true" data-field="components_count"
                                            data-sortable="true">
                                            <i class="far fa-hdd" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.components_count') }}</span>
                                        </th>
                                        <th class="col-sm-1" data-visible="true" data-field="licenses_count"
                                            data-sortable="true">
                                            <i class="far fa-save" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                        </th> --}}
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div> <!-- /.col -->
                            <div class="text-center col-md-12" style="padding-top: 10px;">
                                <a href="{{ route('categories.index') }}" class="btn btn-primary btn-sm"
                                    style="width: 100%">{{ trans('general.viewall') }}</a>
                            </div>
                        </div> <!-- /.row -->

                    </div><!-- /.box-body -->
                </div> <!-- /.box -->
            </div>

            <!-- MODAL TASK -->
            <div class="modal" id="modal-create-alarm">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Create Alarm</h4>
                        </div>
                        <div class="modal-body">
                            <form action="#" onsubmit="return false;">
                                <div class="alert alert-danger" id="modal_error_msg" style="display:none"></div>
                                <div class="form-group row">
                                    <label for="task_name" class="col-sm-3 col-form-label">Task Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="task_name" id="task_name" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="execution_type" class="col-sm-3 col-form-label"><strong>Date
                                            Selection:</strong></label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="execution_type" aria-label="Date Selection">
                                            <option value="current_date_time">Use Current Date & Time</option>
                                            <option value="specific_date">Specific Date & Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row" id="specific_date_input" style="display:none;">
                                    <label for="specific_date_text" class="col-sm-3 col-form-label"></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control flatpickr" name="specific_date_text"
                                            id="specific_date_text" placeholder="YYYY-MM-DD HH:mm">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <a href="#" data-dismiss="modal" class="btn btn-secondary">Close</a>
                            <button type="button" class="btn btn-primary" id="save-task">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif


@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])

@stop

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-timezone-with-data.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <script nonce="{{ csrf_token() }}">
        // ---------------------------
        // - ASSET STATUS CHART -
        // ---------------------------
        var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas);
        var ctx = document.getElementById("statusPieChart");
        var pieOptions = {
            legend: {
                position: 'top',
                responsive: true,
                maintainAspectRatio: true,
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for (var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix + " " + Math.round(counts[tooltipItem.index] / total * 100) + "%";
                    }
                }
            }
        };

        $.ajax({
            type: 'GET',
            url: '{{ \App\Models\Setting::getSettings()->dash_chart_type == 'name' ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
            headers: {
                "X-Requested-With": 'XMLHttpRequest',
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(data) {

                // console.log("API Response Data:", data);
                // Check the structure of data.labels and data.datasets[0].data
                // console.log("Data Labels:", data.labels);
                // console.log("Data Values:", data.datasets[0].data);

                // Define the labels to filter
                var filterLabels = ['Available', 'Booked', 'In Project'];

                // Extract relevant data for the pie chart
                var filteredData = {
                    labels: filterLabels.map(label => {
                        // Extract the count from the label
                        var count = 0;
                        var labelRegex = new RegExp(label + '\\s*\\((\\d+)\\)', 'i');
                        var match = data.labels.find(lbl => lbl.match(labelRegex));

                        if (match) {
                            count = parseInt(match.match(labelRegex)[1]);
                        }

                        // Combine label and count for legend
                        return `${label} (${count})`;
                    }),
                    datasets: [{
                        data: filterLabels.map(label => {
                            // Extract the count from the label
                            var count = 0;
                            var labelRegex = new RegExp(label + '\\s*\\((\\d+)\\)', 'i');
                            var match = data.labels.find(lbl => lbl.match(labelRegex));

                            if (match) {
                                count = parseInt(match.match(labelRegex)[1]);
                            }

                            return count;
                        }),
                        backgroundColor: ['#33FF33', '#FF9933', '#FF3333']
                    }]
                };

                // console.log("Filter Result:", filteredData);

                // Ensure the canvas element exists
                if ($("#statusPieChart").length > 0) {
                    var pieChartCanvas = $("#statusPieChart")[0].getContext("2d");

                    // Destroy the previous chart instance if it exists
                    if (window.myPieChart) {
                        window.myPieChart.destroy();
                    }

                    // Initialize a new Chart.js instance
                    window.myPieChart = new Chart(pieChartCanvas, {
                        type: 'pie',
                        data: filteredData,
                        options: pieOptions
                    });
                }
            },


            error: function(data) {
                // Handle error if needed
            },
        });
    </script>


    {{-- js modal alarm --}}
    <script>
        $(document).ready(function() {
            var tableThirdparty = $('#tableThirdParty').DataTable();
            $('#tableAlarm').DataTable();
            $('#statusFilter').on('change', function() {
                var selectedStatus = this.value;
                tableThirdparty.column(3).search(selectedStatus).draw();
            });
            // Aktifkan flatpickr pada elemen input specific_date_text
            flatpickr("#specific_date_text", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: new Date(),
            });

            $('#execution_type').on('change', function() {
                var specificDateInput = $('#specific_date_input');
                if (this.value === 'specific_date') {
                    specificDateInput.show();
                } else {
                    specificDateInput.hide();
                }
            });

            $('#save-task').on('click', function() {
                saveTask();
            });
        });

        function saveTask() {
            var formData = {
                task_name: $('#task_name').val(),
                execution_type: $('#execution_type').val(),
                specific_date_text: ($('#execution_type').val() === 'specific_date') ? $('#specific_date_text').val() :
                    getCurrentDateTime(),
            };

            $.ajax({
                url: "/save-task",
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: JSON.stringify(formData),
                success: function(response) {
                    $("#modal-create-alarm").modal("hide");
                    showSuccess("Task saved successfully.");
                },
                error: function(error) {
                    showError("Failed to save task. Please try again.");
                },
            });
        }

        function showError(message) {
            $('#modal_error_msg').text(message).show();
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Save task',
                text: message,
            });
            location.reload();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('.form-check-input');

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var alarmId = this.getAttribute('data-id');
                    var isChecked = this.checked;

                    fetch('/update-alarm-status/' + alarmId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                status: isChecked
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data.message);

                            // Tampilkan notifikasi pop-up jika sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Update Status',
                                text: data.message,
                            });

                            // Me-refresh halaman
                            location.reload();
                        })
                        .catch(error => {
                            console.error('Request failed', error);

                            // Tampilkan notifikasi pop-up jika gagal
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Status Failed',
                                text: 'There was an error updating the status.',
                            });
                        });
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.flatpickr', {
                enableTime: true,
                dateFormat: "Y-m-d H:i:s",
                defaultDate: "today",
            });
            var checkboxes = document.querySelectorAll('.form-check-input');
            var deleteButtons = document.querySelectorAll('.delete-alarm-btn');

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var alarmId = this.getAttribute('data-id');
                    var isChecked = this.checked;

                    updateAlarmStatus(alarmId, isChecked);
                });
            });

            deleteButtons.forEach(function(deleteButton) {
                deleteButton.addEventListener('click', function() {
                    var alarmId = this.getAttribute('data-id');

                    // Tampilkan konfirmasi SweetAlert2 sebelum menghapus
                    Swal.fire({
                        title: 'Delete Alarm',
                        text: 'Are you sure you want to delete this alarm?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Jika pengguna mengonfirmasi, panggil fungsi untuk menghapus
                            deleteAlarm(alarmId);
                        }
                    });
                });
            });

            function updateAlarmStatus(alarmId, isChecked) {

                fetch('/update-alarm-status/' + alarmId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                        },
                        body: JSON.stringify({
                            status: isChecked
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message);

                        // Tampilkan notifikasi pop-up jika sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Update Status',
                            text: data.message,
                        });

                        // Me-refresh halaman
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Request failed', error);

                        // Tampilkan notifikasi pop-up jika gagal
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Status Failed',
                            text: 'There was an error updating the status.',
                        });
                    });
            }

            function deleteAlarm(alarmId) {
                fetch('/delete-alarm/' + alarmId, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message);

                        // Tampilkan notifikasi pop-up jika sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Delete Alarm',
                            text: data.message,
                        });

                        // Me-refresh halaman
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Request failed', error);

                        // Tampilkan notifikasi pop-up jika gagal
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Alarm Failed',
                            text: 'There was an error deleting the alarm.',
                        });
                    });
            }
        });


        function getCurrentDateTime() {
            var now = new Date();
            var formattedDate = moment(now).tz('America/New_York').format(
                'YYYY-MM-DD HH:mm:ss'
            );

            return formattedDate;
        }
    </script>
@endpush
