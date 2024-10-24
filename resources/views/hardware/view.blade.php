@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.view') }} {{ $asset->asset_tag }}
    @parent
@stop
@push('css')
    {{-- <style>
        .container.gallery-container {
            background-color: #fff;
            color: #35373a;
            min-height: 100vh;
            padding: 30px 50px;
        }

        .gallery-container h1 {
            text-align: center;
            margin-top: 50px;
            font-family: 'Droid Sans', sans-serif;
            font-weight: bold;
        }

        .gallery-container p.page-description {
            text-align: center;
            margin: 25px auto;
            font-size: 18px;
            color: #999;
        }

        .tz-gallery {
            width: 300px;
            /* padding: 10px; */
        }

        /* Override bootstrap column paddings */
        .tz-gallery .row>div {
            padding: 2px;
        }

        .tz-gallery .lightbox img {
            width: 100%;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .tz-gallery .lightbox:before {
            content: '\f03e';
            /* Unicode for the image icon (you can change this) */
            font-family: FontAwesome;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -25px;
            /* Adjust this value to center the icon */
            margin-left: -25px;
            /* Adjust this value to center the icon */
            font-size: 50px;
            color: rgba(255, 255, 255, 0.8);
            opacity: 0;
            transition: 0.4s;
            z-index: 9000;
        }

        .tz-gallery .lightbox:after {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            background-color: rgba(46, 132, 206, 0.7);
            content: '';
            transition: 0.4s;
        }

        .tz-gallery .lightbox:hover:after,
        .tz-gallery .lightbox:hover:before {
            opacity: 1;
        }

        .baguetteBox-button {
            background-color: transparent !important;
        }

        @media(max-width: 768px) {
            body {
                padding: 0;
            }
        }
    </style> --}}
    <style>
        /* Hide the images by default */
        .mySlides {
            display: none;
        }

        /* Add a pointer when hovering over the thumbnail images */
        .cursor {
            cursor: pointer;
        }

        /* Next & previous buttons */
        .prev-img,
        .next-img {
            cursor: pointer;
            position: absolute;
            top: 60%;
            width: auto;
            color: rgb(0, 136, 255)0, 153, 255);
            padding: 16px;
            text-decoration: none;
            margin-top: -50px;
            font-weight: bold;
            font-size: 20px;
            border-radius: 0 3px 3px 0;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Position the "next button" to the right */
        .next-img {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        /* On hover, add a black background color with a little bit see-through */
        .prev-img:hover,
        .next-img:hover {
            color: white;
            background-color: rgba(96, 96, 96, 0.8);
        }

        /* Number text (1/3 etc) */
        .numbertext {
            color: #393939;
            font-size: 12px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
        }

        /* Container for image text */
        .caption-container {
            text-align: center;
            background-color: #222;
            padding: 2px 16px;
            color: white;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Six columns side by side */
        .column {
            float: left;
            width: 16.66%;
        }

        /* Add a transparency effect for thumnbail images */
        .demo {
            opacity: 0.6;
        }

        .active,
        .demo:hover {
            opacity: 1;
        }
    </style>
@endpush

{{-- Right header --}}
@section('header_right')
    @can('manage', \App\Models\Asset::class)
        @if ($asset->deleted_at == '')
            @push('css')
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
            @endpush
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
            <link href="https://fonts.googleapis.com/css?family=Droid+Sans:400,700" rel="stylesheet">
            <link rel="stylesheet" href="https://rawgit.com/LeshikJanz/libraries/master/Bootstrap/baguetteBox.min.css">
            <div class="dropdown pull-right">
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right" role="menu">

                    {{-- @if ($asset->assetstatus && $asset->assetstatus->deployable == '1')
                        @if ($asset->assigned_to != '' && $asset->deleted_at == '')
                            @can('checkin', \App\Models\Asset::class)
                                <li role="menuitem">
                                    <a href="{{ route('hardware.checkin.create', $asset->id) }}">
                                        {{ trans('admin/hardware/general.checkin') }}
                                    </a>
                                </li>
                            @endcan
                        @elseif ($asset->assigned_to == '' && $asset->deleted_at == '')
                            @can('checkout', \App\Models\Asset::class)
                                <li role="menuitem">
                                    <a href="{{ route('hardware.checkout.create', $asset->id) }}">
                                        {{ trans('admin/hardware/general.checkout') }}
                                    </a>
                                </li>
                            @endcan
                        @endif
                    @endif --}}

                    @can('update', \App\Models\Asset::class)
                        <li role="menuitem">
                            <a href="{{ route('hardware.edit', $asset->id) }}">
                                {{ trans('admin/hardware/general.edit') }}
                            </a>
                        </li>
                    @endcan

                    {{-- @can('create', \App\Models\Asset::class)
                    <li role="menuitem">
                        <a href="{{ route('clone/hardware', $asset->id) }}">
                        {{ trans('admin/hardware/general.clone') }}
                    </a>
                            </li>
                         @endcan --}}

                    {{-- @can('audit', \App\Models\Asset::class)
                        <li role="menuitem">
                            <a href="{{ route('asset.audit.create', $asset->id) }}">
                                {{ trans('general.audit') }}
                            </a>
                        </li>
                    @endcan --}}
                </ul>
            </div>
        @endif
    @endcan
@stop

{{-- Page content --}}
@section('content')

    <div class="row">

        @if (!$asset->model)
            <div class="col-md-12">
                <div class="callout callout-danger">
                    <h2>{{ trans('admin/models/message.no_association') }}</h2>
                    <p>{{ trans('admin/models/message.no_association_fix') }}</p>
                </div>
            </div>
        @endif

        @if ($asset->deleted_at != '')
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle faa-pulse animated" aria-hidden="true"></i>
                    <strong>{{ trans('general.notification_warning') }} </strong>
                    {{ trans('general.asset_deleted_warning') }}
                </div>
            </div>
        @endif

        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#details" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">{{ trans('admin/users/general.info') }}</span>
                        </a>
                    </li>

                    {{-- <li>
                        <a href="#software" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <i class="far fa-save fa-2x" aria-hidden="true"></i>
                          </span>
                          <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
                        {!! ($asset->licenses->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($asset->licenses->count()).'</badge>' : '' !!}
                        </span>
                        </a>
                        </li> --}}

                    {{-- <li>
                        <a href="#components" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <i class="far fa-hdd fa-2x" aria-hidden="true"></i>
                          </span>
                          <span class="hidden-xs hidden-sm">{{ trans('general.components') }}
                         {!! ($asset->components->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($asset->components->count()).'</badge>' : '' !!}
                            </span>
                            </a>
                                </li> --}}

                    {{-- <li>
                        <a href="#assets" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <i class="fas fa-barcode fa-2x" aria-hidden="true"></i>
                          </span>
                          <span class="hidden-xs hidden-sm">{{ trans('general.assets') }}
                            {!! ($asset->assignedAssets()->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($asset->assignedAssets()->count()).'</badge>' : '' !!}

                            </span>
                         </a>
                         </li> --}}


                    <li>
                        <a href="#history" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <i class="fas fa-history fa-2x" aria-hidden="true"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">{{ trans('general.history') }}
                            </span>
                        </a>
                    </li>

                    {{-- <li>
                        <a href="#maintenances" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                            <i class="fas fa-wrench fa-2x" aria-hidden="true"></i>
                          </span>
                          <span class="hidden-xs hidden-sm">{{ trans('general.maintenances') }}
                            {!! ($asset->assetmaintenances()->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($asset->assetmaintenances()->count()).'</badge>' : '' !!}
                             </span>
                             </a>
                             </li> --}}

                    <li>
                        <a href="#files" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <i class="far fa-file fa-2x" aria-hidden="true"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">Asset {{ trans('general.files') }}
                                {!! $asset->uploads->count() > 0
                                    ? '<badge class="badge badge-secondary">' . number_format($asset->uploads->count()) . '</badge>'
                                    : '' !!}
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="#listimages" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                                <i class="far fa-file fa-2x" aria-hidden="true"></i>
                            </span>
                            <span class="hidden-xs hidden-sm">Image
                            </span>
                        </a>
                    </li>

                    {{-- <li>
                             <a href="#modelfiles" data-toggle="tab">
                          <span class="hidden-lg hidden-md">
                              <i class="fa-solid fa-laptop-file fa-2x" aria-hidden="true"></i>
                          </span>
                        <span class="hidden-xs hidden-sm">
                            {{ trans('general.additional_files') }}
                                 {!! ($asset->model->uploads->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($asset->model->uploads->count()).'</badge>' : '' !!}
                             </span>
                             </a>
                            </li> --}}


                    @can('update', \App\Models\Asset::class)
                        <li class="pull-right">
                            <a href="#" data-toggle="modal" data-target="#uploadFileModal">
                                <i class="fas fa-paperclip" aria-hidden="true"></i>
                                {{ trans('button.upload_related') }}
                            </a>
                        </li>
                    @endcan
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade in active" id="details">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- start striped rows -->
                                <div class="container row-striped">
                                    @if ($asset->deleted_at != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <span
                                                    class="text-danger"><strong>{{ trans('general.deleted') }}</strong></span>
                                            </div>
                                            <div class="col-md-6">
                                                {{ \App\Helpers\Helper::getFormattedDateObject($asset->deleted_at, 'date', false) }}

                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->assetstatus)

                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>{{ trans('general.status') }}</strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->assignedTo && $asset->deleted_at == '')
                                                    <i class="fas fa-circle text-blue"></i>
                                                    {{ $asset->assetstatus->name }}
                                                    <label
                                                        class="label label-default">{{ trans('general.deployed') }}</label>

                                                    <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i>
                                                    {!! $asset->assignedTo->present()->glyph() !!}
                                                    {!! $asset->assignedTo->present()->nameUrl() !!}
                                                @else
                                                    @if ($asset->assetstatus && $asset->assetstatus->deployable == '1')
                                                        <i class="fas fa-circle text-green"></i>
                                                    @elseif ($asset->assetstatus && $asset->assetstatus->pending == '1')
                                                        <i class="fas fa-circle text-orange"></i>
                                                    @else
                                                        <i class="fas fa-times text-red"></i>
                                                    @endif
                                                    <a href="{{ route('statuslabels.show', $asset->assetstatus->id) }}">
                                                        {{ $asset->assetstatus->name }}</a>
                                                    <label
                                                        class="label label-default">{{ $asset->present()->statusMeta }}</label>

                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->company)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>{{ trans('general.company') }}</strong>
                                            </div>
                                            <div class="col-md-5">
                                                <a
                                                    href="{{ url('/companies/' . $asset->company->id) }}">{{ $asset->company->name }}</a>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->name)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>{{ trans('admin/hardware/form.name') }}</strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->name }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->asset_tag)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>{{ trans('general.asset_number') }}</strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->asset_tag }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->serial)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>{{ trans('admin/hardware/form.serial') }}</strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->serial }}
                                            </div>
                                        </div>
                                    @endif

                                    @if (isset($audit_log) && $audit_log->created_at)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.last_audit') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ \App\Helpers\Helper::getFormattedDateObject($audit_log->created_at, 'date', false) }}
                                                @if ($audit_log->user)
                                                    (by
                                                    {{ link_to_route('users.show', $audit_log->user->present()->fullname(), [$audit_log->user->id]) }})
                                                @endif

                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->next_audit_date)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.next_audit_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->model && $asset->model->manufacturer)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.manufacturer') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                <ul class="list-unstyled">
                                                    @can('view', \App\Models\Manufacturer::class)
                                                        <li>
                                                            <a
                                                                href="{{ route('manufacturers.show', $asset->model->manufacturer->id) }}">
                                                                {{ $asset->model->manufacturer->name }}
                                                            </a>
                                                        </li>
                                                    @else
                                                        <li> {{ $asset->model->manufacturer->name }}</li>
                                                    @endcan

                                                    @if ($asset->model && $asset->model->manufacturer->url)
                                                        <li>
                                                            <i class="fas fa-globe-americas" aria-hidden="true"></i>
                                                            <a href="{{ $asset->model->manufacturer->url }}">
                                                                {{ $asset->model->manufacturer->url }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($asset->model && $asset->model->manufacturer->support_url)
                                                        <li>
                                                            <i class="far fa-life-ring" aria-hidden="true"></i>
                                                            <a href="{{ $asset->model->manufacturer->support_url }}">
                                                                {{ $asset->model->manufacturer->support_url }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($asset->model && $asset->model->manufacturer->support_phone)
                                                        <li>
                                                            <i class="fas fa-phone" aria-hidden="true"></i>
                                                            <a
                                                                href="tel:{{ $asset->model->manufacturer->support_phone }}">
                                                                {{ $asset->model->manufacturer->support_phone }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($asset->model && $asset->model->manufacturer->support_email)
                                                        <li>
                                                            <i class="far fa-envelope" aria-hidden="true"></i>
                                                            <a
                                                                href="mailto:{{ $asset->model->manufacturer->support_email }}">
                                                                {{ $asset->model->manufacturer->support_email }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('general.category') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            @if ($asset->model && $asset->model->category)
                                                @can('view', \App\Models\Category::class)
                                                    <a href="{{ route('categories.show', $asset->model->category->id) }}">
                                                        {{ $asset->model->category->name }}
                                                    </a>
                                                @else
                                                    {{ $asset->model->category->name }}
                                                @endcan
                                            @else
                                                Invalid category
                                            @endif
                                        </div>
                                    </div>
                                    @if ($asset->model)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.model') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->model)
                                                    @can('view', \App\Models\AssetModel::class)
                                                        <a href="{{ route('family.show', $asset->model->id) }}">
                                                            {{ $asset->model->name }}
                                                        </a>
                                                    @else
                                                        {{ $asset->model->name }}
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('admin/models/table.modelnumber') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            {{ $asset->model ? $asset->model->model_number : '' }}
                                        </div>
                                    </div>
                                    <!-- byod -->
                                    {{-- <div class="row">
                                        <div class="col-md-2">
                                            <strong>{{ trans('general.byod') }}</strong>
                                     </div>
                                         <div class="col-md-6">
                                          {!! ($asset->byod=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"></i> '.trans('general.yes') : '<i class="fas fa-times text-danger" aria-hidden="true"></i> '.trans('general.no') !!}
                                          </div>
                                         </div> --}}
                                    @if ($asset->model && $asset->model->fieldset)
                                        @foreach ($asset->model->fieldset->fields as $field)
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <strong>
                                                        {{ $field->name }}
                                                    </strong>
                                                </div>
                                                <div
                                                    class="col-md-5{{ $field->format == 'URL' && $asset->{$field->db_column_name()} != '' ? ' ellipsis' : '' }}">
                                                    @if ($field->field_encrypted == '1')
                                                        <i class="fas fa-lock" data-tooltip="true" data-placement="top"
                                                            title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
                                                    @endif

                                                    @if ($field->isFieldDecryptable($asset->{$field->db_column_name()}))
                                                        @can('superuser')
                                                            @if ($field->format == 'URL' && $asset->{$field->db_column_name()} != '')
                                                                <a href="{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}"
                                                                    target="_new">{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}</a>
                                                            @elseif ($field->format == 'DATE' && $asset->{$field->db_column_name()} != '')
                                                                {{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false)) }}
                                                            @else
                                                                {{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}
                                                            @endif
                                                        @else
                                                            {{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}
                                                        @endcan
                                                    @else
                                                        @if ($field->format == 'BOOLEAN' && $asset->{$field->db_column_name()} != '')
                                                            {!! $asset->{$field->db_column_name()} == 1
                                                                ? "<span class='fas fa-check-circle' style='color:green' />"
                                                                : "<span class='fas fa-times-circle' style='color:red' />" !!}
                                                        @elseif ($field->format == 'URL' && $asset->{$field->db_column_name()} != '')
                                                            <a href="{{ $asset->{$field->db_column_name()} }}"
                                                                target="_new">{{ $asset->{$field->db_column_name()} }}</a>
                                                        @elseif ($field->format == 'DATE' && $asset->{$field->db_column_name()} != '')
                                                            {{ \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false) }}
                                                        @else
                                                            {!! nl2br(e($asset->{$field->db_column_name()})) !!}
                                                        @endif
                                                    @endif

                                                    @if ($asset->{$field->db_column_name()} == '')
                                                        &nbsp;
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if ($asset->purchase_date)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->purchase_date, 'date', false) }}
                                                -
                                                {{ Carbon::parse($asset->purchase_date)->diff(Carbon::now())->format('%y years, %m months and %d days') }}

                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->purchase_cost)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.cost') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @elseif ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @else
                                                    {{ $snipeSettings->default_currency }}
                                                @endif
                                                {{ Helper::formatCurrencyOutput($asset->purchase_cost) }}

                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->components->count() > 0 && $asset->purchase_cost)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/table.components_cost') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @elseif ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @else
                                                    {{ $snipeSettings->default_currency }}
                                                @endif
                                                {{ Helper::formatCurrencyOutput($asset->getComponentCost()) }}
                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->model && $asset->depreciation && $asset->purchase_date)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/table.current_value') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @elseif ($asset->id && $asset->location)
                                                    {{ $asset->location->currency }}
                                                @else
                                                    {{ $snipeSettings->default_currency }}
                                                @endif
                                                {{ Helper::formatCurrencyOutput($asset->getDepreciatedValue()) }}


                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->order_number)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.order_number') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                <a
                                                    href="{{ route('hardware.index', ['order_number' => $asset->order_number]) }}">#{{ $asset->order_number }}</a>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- @if ($asset->supplier)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.supplier') }}
                                                </strong>   
                                            </div>
                                            <div class="col-md-5">
                                                @can('superuser')
                                                    <a href="{{ route('suppliers.show', $asset->supplier_id) }}">
                                                        {{ $asset->supplier->name }}
                                                    </a>
                                                @else
                                                    {{ $asset->supplier->name }}
                                                @endcan
                                            </div>
                                        </div>
                                    @endif --}}
                                    @if ($asset->warranty_months)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.warranty') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->warranty_months }}
                                                {{ trans('admin/hardware/form.months') }}

                                                @if ($asset->model->manufacturer && $asset->model->manufacturer->warranty_lookup_url != '')
                                                    <a href="{{ $asset->present()->dynamicWarrantyUrl() }}"
                                                        target="_blank">
                                                        <i class="fa fa-external-link" aria-hidden="true"><span
                                                                class="sr-only">{{ trans('admin/hardware/general.mfg_warranty_lookup', ['manufacturer' => $asset->model->manufacturer->name]) }}</span></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.warranty_expires') }}
                                                    @if ($asset->purchase_date)
                                                        {!! $asset->present()->warranty_expires() < date('Y-m-d')
                                                            ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>'
                                                            : '' !!}
                                                    @endif
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->purchase_date)
                                                    {{ Helper::getFormattedDateObject($asset->present()->warranty_expires(), 'date', false) }}
                                                    -
                                                    {{ Carbon::parse($asset->present()->warranty_expires())->diffForHumans(['parts' => 2]) }}
                                                @else
                                                    {{ trans('general.na_no_purchase_date') }}
                                                @endif
                                            </div>
                                        </div>

                                    @endif
                                    @if ($asset->model && $asset->depreciation)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.depreciation') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->depreciation->name }}
                                                ({{ $asset->depreciation->months }}
                                                {{ trans('admin/hardware/form.months') }})
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.fully_depreciated') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->purchase_date)
                                                    {{ Helper::getFormattedDateObject($asset->depreciated_date()->format('Y-m-d'), 'date', false) }}
                                                    -
                                                    {{ Carbon::parse($asset->depreciated_date())->diffForHumans(['parts' => 2]) }}
                                                @else
                                                    {{ trans('general.na_no_purchase_date') }}
                                                @endif

                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->model && $asset->model->eol)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.eol_rate') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ $asset->model->eol }}
                                                {{ trans('admin/hardware/form.months') }}

                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->asset_eol_date)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.eol_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->asset_eol_date)
                                                    {{ Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) }}
                                                    -
                                                    {{ Carbon::parse($asset->asset_eol_date)->diffForHumans(['parts' => 2]) }}
                                                @else
                                                    {{ trans('general.na_no_purchase_date') }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->expected_checkin != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.expected_checkin') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('admin/hardware/form.notes') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            {{-- {!! nl2br(e($asset->notes)) !!} --}}
                                            {{ $asset->notes ?? '-' }}
                                        </div>
                                    </div>
                                    @if ($asset->location)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.location') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @can('superuser')
                                                    <a
                                                        href="{{ route('locations.show', ['location' => $asset->location->id]) }}">
                                                        {{ $asset->location->name }}
                                                    </a>
                                                @else
                                                    {{ $asset->location->name }}
                                                @endcan
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->defaultLoc)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.default_location') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @can('superuser')
                                                    <a
                                                        href="{{ route('locations.show', ['location' => $asset->defaultLoc->id]) }}">
                                                        {{ $asset->defaultLoc->name }}
                                                    </a>
                                                @else
                                                    {{ $asset->defaultLoc->name }}
                                                @endcan
                                            </div>
                                        </div>
                                    @endif

                                    {{-- @if ($asset->created_at != '')
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('general.created_at') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            {{ Helper::getFormattedDateObject($asset->created_at, 'datetime', false) }}
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ($asset->model && $asset->depreciation)
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('general.depreciation') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            {{ $asset->depreciation->name }}
                                            ({{ $asset->depreciation->months }}
                                            {{ trans('admin/hardware/form.months') }})
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('admin/hardware/form.fully_depreciated') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            @if ($asset->purchase_date)
                                                {{ Helper::getFormattedDateObject($asset->depreciated_date()->format('Y-m-d'), 'date', false) }}
                                                -
                                                {{ Carbon::parse($asset->depreciated_date())->diffForHumans(['parts' => 2]) }}
                                            @else
                                                {{ trans('general.na_no_purchase_date') }}
                                            @endif

                                        </div>
                                    </div>
                                @endif --}}

                                    {{-- @if ($asset->model && $asset->model->eol)
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('admin/hardware/form.eol_rate') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            {{ $asset->model->eol }}
                                            {{ trans('admin/hardware/form.months') }}

                                        </div>
                                    </div>
                                @endif --}}
                                    @if ($asset->asset_eol_date)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.eol_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                @if ($asset->asset_eol_date)
                                                    {{ Helper::getFormattedDateObject($asset->asset_eol_date, 'date', false) }}
                                                    -
                                                    {{ Carbon::parse($asset->asset_eol_date)->diffForHumans(['parts' => 2]) }}
                                                @else
                                                    {{ trans('general.na_no_purchase_date') }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->expected_checkin != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/form.expected_checkin') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                            </div>
                                        </div>
                                    @endif

                                    {{-- <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.notes') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {!! nl2br(e($asset->notes)) !!}
                                    </div>
                                </div> --}}

                                    {{-- @if ($asset->location)
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.location') }}
                                    </strong>
                                     </div>
                                     <div class="col-md-6">
                                    @can('superuser')
                                    <a href="{{ route('locations.show', ['location' => $asset->location->id]) }}">
                                        {{ $asset->location->name }}
                                    </a>
                                          @else
                                         {{ $asset->location->name }}
                                      @endcan
                                         </div>
                                            </div>
                                         @endif --}}

                                    {{-- @if ($asset->defaultLoc)
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('admin/hardware/form.default_location') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            @can('superuser')
                                                <a
                                                    href="{{ route('locations.show', ['location' => $asset->defaultLoc->id]) }}">
                                                    {{ $asset->defaultLoc->name }}
                                                </a>
                                            @else
                                                {{ $asset->defaultLoc->name }}
                                            @endcan
                                        </div>
                                    </div>
                                @endif --}}
                                    @if ($asset->created_at != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.created_at') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->created_at, 'datetime', false) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($asset->updated_at != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('general.updated_at') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->updated_at, 'datetime', false) }}
                                            </div>
                                        </div>
                                    @endif
                                    @if ($asset->last_checkout != '')
                                        <div class="row">
                                            <div class="col-md-2">
                                                <strong>
                                                    {{ trans('admin/hardware/table.checkout_date') }}
                                                </strong>
                                            </div>
                                            <div class="col-md-5">
                                                {{ Helper::getFormattedDateObject($asset->last_checkout, 'datetime', false) }}
                                            </div>
                                        </div>
                                    @endif



                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('general.checkouts_count') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            {{-- {{ $asset->checkouts ? (int) $asset->checkouts->count() : '0' }} --}}
                                            {{ $asset->checkout_counter }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ trans('general.checkins_count') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            {{-- {{ $asset->checkins ? (int) $asset->checkins->count() : '0' }} --}}
                                            {{ $asset->checkin_counter }}
                                        </div>
                                    </div>

                                    {{-- <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                                {{ trans('general.user_requests_count') }}
                                                 </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->userRequests ? (int) $asset->userRequests->count() : '0' }}
                                    </div>
                                </div> --}}
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                Labels
                                            </strong>
                                        </div>
                                        <div class="col-md-5">
                                            {{ Form::open([
                                                'method' => 'POST',
                                                'route' => ['hardware/bulkedit'],
                                                'class' => 'form-inline',
                                                'id' => 'bulkForm',
                                                'target' => '_blank',
                                            ]) }}
                                            <input type="hidden" name="bulk_actions" value="labels" />
                                            <input type="hidden" name="ids[{{ $asset->id }}]"
                                                value="{{ $asset->id }}" />
                                            <button class="btn btn-sm btn-default" id="bulkEdit"><i
                                                    class="fas fa-barcode" aria-hidden="true"></i>
                                                {{ trans_choice('button.generate_labels', 1) }}</button>

                                            {{ Form::close() }}

                                        </div>
                                    </div>
                                </div> <!-- end striped rows -->
                            </div><!-- /col-md-8 -->
                            {{-- Ini utk image dikanan --}}
                            <div class="col-md-4">

                                {{-- @if ($asset->image || ($asset->model && $asset->model->image != '')) --}}


                                {{-- <div class="text-center col-md-12" style="padding-bottom: 15px;">
                                        <div class="tz-gallery">
                                            @foreach ($images as $image)
                                                @if (count($images) == 1)
                                                    <div class="col-md-12">
                                                    @elseif (count($images) == 2)
                                                        <div class="col-md-6">
                                                        @else
                                                            <div class="col-sm-6 col-md-4">
                                                @endif
                                                <a class="lightbox" href="{{ $image->getImageUrl2() }}">
                                                    <img src="{{ $image->getImageUrl2() }}" alt="Image">
                                                </a>
                                            @endforeach
                                        </div>
                                        
                                    </div> --}}

                                {{-- @endif --}}

                                @if (count($images) > 0)
                                    <div style="position: relative">
                                        <div class="text-center" style="padding-bottom: 15px;">
                                            <h3>Image Asset</h3>
                                        </div>
                                        {{-- @foreach ($images as $image)
                                            <div class="text-center col-md-12"
                                                style="padding-bottom: 15px; display: block;">
                                                <a href="{{ $image->getImageUrl2() ? $image->getImageUrl2() : null }}"
                                                    data-toggle="lightbox">
                                                    <img src="{{ $image->getImageUrl2() ? $image->getImageUrl2() : null }}"
                                                        class="assetimg img-responsive"
                                                        alt="{{ $asset->getDisplayNameAttribute() }}">
                                                </a>
                                            </div>
                                        @break --}}
                                        {{-- @endforeach --}}
                                        @foreach ($images as $index => $image)
                                            <div class="mySlides" style="padding-bottom: 15px;">
                                                {{-- <div class="numbertext">{{ $index + 1 }} / {{ count($images) }}
                                                </div> --}}
                                                <a href="{{ $image->getImageUrl2() ? $image->getImageUrl2() : null }}"
                                                    data-toggle="lightbox">
                                                    <img src="{{ $image->getImageUrl2() ? $image->getImageUrl2() : null }}"
                                                        style="width:100%;height: 400px;object-fit: cover;"
                                                        alt="{{ $asset->getDisplayNameAttribute() }}">
                                                </a>
                                            </div>
                                        @endforeach
                                        @if (count($images) > 1)
                                            <a class="prev-img" onclick="plusSlides(-1)">❮</a>
                                            <a class="next-img" onclick="plusSlides(1)">❯</a>
                                        @endif
                                    </div>
                                @endif




                                @if ($asset->deleted_at != '')
                                    <div class="text-center col-md-12" style="padding-bottom: 15px;">
                                        <form method="POST"
                                            action="{{ route('restore/hardware', ['assetId' => $asset->id]) }}">
                                            @csrf
                                            <button
                                                class="btn btn-danger col-md-12">{{ trans('general.restore') }}</button>
                                        </form>
                                    </div>
                                @endif
                                @if ($snipeSettings->qr_code == '1')
                                    <div class="box pull-right"
                                        style="height: 100  px; width: 100px; margin-right: 10px;">
                                        <div class="box-body">
                                            {!! DNS2D::getBarcodeHTML($asset->asset_tag, 'QRCODE', 4, 4) !!}
                                        </div>
                                    </div>
                                    {{-- <img src="{{ config('app.url') }}/hardware/{{ $asset->id }}/qr_code"
                                    class="img-thumbnail pull-right"
                                    style="height: 100px; width: 100px; margin-right: 10px;"
                                    alt="QR code for {{ $asset->getDisplayNameAttribute() }}"> --}}
                                @endif

                                @if ($asset->assignedTo && $asset->deleted_at == '')
                                    <h2>{{ trans('admin/hardware/form.checkedout_to') }}</h2>
                                    <p>
                                        @if ($asset->checkedOutToUser())
                                            <!-- Only users have avatars currently-->
                                            <img src="{{ $asset->assignedTo->present()->gravatar() }}"
                                                class="user-image-inline"
                                                alt="{{ $asset->assignedTo->present()->fullName() }}">
                                        @endif
                                        @if ($asset->checkoutToMovement())
                                            <!-- Only users have avatars currently-->
                                            {{-- <img src="{{ $asset->assignedTo->present()->gravatar() }}"
                                        class="user-image-inline"
                                        alt="{{ $asset->assignedTo->present()->fullName() }}"> --}}
                                            <h1>Movement {!! $asset->assignedTo->present()->nameUrl() !!}</h1>
                                        @endif
                                        {!! $asset->assignedTo->present()->glyph() . ' ' . $asset->assignedTo->present()->nameUrl() !!}
                                    </p>

                                    <ul class="list-unstyled" style="line-height: 25px;">
                                        @if (isset($asset->assignedTo->email) && $asset->assignedTo->email != '')
                                            <li>
                                                <i class="far fa-envelope" aria-hidden="true"></i>
                                                <a
                                                    href="mailto:{{ $asset->assignedTo->email }}">{{ $asset->assignedTo->email }}</a>
                                            </li>
                                        @endif

                                        @if (isset($asset->assignedTo) && $asset->assignedTo->phone != '')
                                            <li>
                                                <i class="fas fa-phone" aria-hidden="true"></i>
                                                <a
                                                    href="tel:{{ $asset->assignedTo->phone }}">{{ $asset->assignedTo->phone }}</a>
                                            </li>
                                        @endif

                                        @if (isset($asset->location))
                                            <li>{{ $asset->location->name }}</li>
                                            <li>{{ $asset->location->address }}
                                                @if ($asset->location->address2 != '')
                                                    {{ $asset->location->address2 }}
                                                @endif
                                            </li>

                                            <li>{{ $asset->location->city }}
                                                @if ($asset->location->city != '' && $asset->location->state != '')
                                                    ,
                                                @endif
                                                {{ $asset->location->state }} {{ $asset->location->zip }}
                                            </li>
                                        @endif
                                        <li>
                                            <i class="fas fa-calendar"></i>
                                            {{ trans('admin/hardware/form.checkout_movement_date') }}:
                                            {{ Helper::getFormattedDateObject($asset->last_checkout, 'date', false) }}
                                        </li>
                                        @if (isset($asset->expected_checkin))
                                            <li>
                                                <i class="fas fa-calendar"></i>
                                                {{ trans('admin/hardware/form.expected_checkin') }}:
                                                {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
                                            </li>
                                        @endif
                                    </ul>

                                @endif
                                @can('audit', \App\Models\Asset::class)
                                    <div class="col-md-12" style="padding-top: 5px;">
                                        <a href="{{ route('asset.audit.create', $asset->id) }}" style="width: 100%;"
                                            class="btn btn-sm btn-primary hidden-print">
                                            {{ trans('general.audit') }}
                                        </a>
                                    </div>
                                @endcan

                            </div>
                        </div><!-- /col-md-3 -->
                    </div><!---/tab pane active-->
                    {{-- <div class="tab-pane fade" id="assets">
                    <div class="row">
                        <div class="col-md-12">

                            @if ($asset->assignedAssets->count() > 0)
                                {{ Form::open([
                                    'method' => 'POST',
                                    'route' => ['hardware/bulkedit'],
                                    'class' => 'form-inline',
                                    'id' => 'bulkForm',
                                ]) }}
                                <div id="toolbar">
                                    <label for="bulk_actions"><span
                                            class="sr-only">{{ trans('general.bulk_actions') }}</span></label>
                                    <select name="bulk_actions" class="form-control select2" style="width: 150px;"
                                        aria-label="bulk_actions">
                                        <option value="edit">{{ trans('button.edit') }}</option>
                                        <option value="delete">{{ trans('button.delete') }}</option>
                                        <option value="labels">{{ trans_choice('button.generate_labels', 2) }}</option>
                                    </select>
                                    <button class="btn btn-primary" id="bulkEdit"
                                        disabled>{{ trans('button.go') }}</button>
                                </div>

                                <!-- checked out assets table -->
                                <div class="table-responsive">

                                    <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="assetsTable" data-pagination="true"
                                        data-id-table="assetsTable" data-search="true" data-side-pagination="server"
                                        data-show-columns="true" data-show-fullscreen="true" data-show-export="true"
                                        data-show-refresh="true" data-sort-order="asc" id="assetsListingTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.assets.index', ['assigned_to' => $asset->id, 'assigned_type' => 'App\Models\Asset']) }}"
                                        data-export-options='{
                              "fileName": "export-assets-{{ str_slug($asset->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                                    </table>


                                    {{ Form::close() }}
                                </div>
                            @else
                                <div class="alert alert-info alert-block">
                                    <i class="fas fa-info-circle"></i>
                                    {{ trans('general.no_results') }}
                                </div>
                            @endif


                        </div><!-- /col -->
                    </div> <!-- row -->
                </div> <!-- /.tab-pane software -->
                <div class="tab-pane fade" id="maintenances">
                    <div class="row">
                        <div class="col-md-12">
                            @can('update', \App\Models\Asset::class)
                                <div id="maintenance-toolbar">
                                    <a href="{{ route('maintenances.create', ['asset_id' => $asset->id]) }}"
                                        class="btn btn-primary">{{ trans('button.add_maintenance') }}</a>
                                </div>
                            @endcan

                            <!-- Asset Maintenance table -->
                            <table data-columns="{{ \App\Presenters\AssetMaintenancesPresenter::dataTableLayout() }}"
                                class="table table-striped snipe-table" id="assetMaintenancesTable"
                                data-pagination="true" data-id-table="assetMaintenancesTable" data-search="true"
                                data-side-pagination="server" data-toolbar="#maintenance-toolbar"
                                data-show-columns="true" data-show-fullscreen="true" data-show-refresh="true"
                                data-show-export="true"
                                data-export-options='{
                           "fileName": "export-{{ $asset->asset_tag }}-maintenances",
                           "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                         }'
                                data-url="{{ route('api.maintenances.index', ['asset_id' => $asset->id]) }}"
                                data-cookie-id-table="assetMaintenancesTable" data-cookie="true">
                            </table>
                        </div> <!-- /.col-md-12 -->
                    </div> <!-- /.row -->
                </div> <!-- /.tab-pane maintenances --> --}}

                    <div class="tab-pane fade" id="history">
                        <!-- checked out assets table -->
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-striped snipe-table" id="assetHistory"
                                            data-pagination="true" data-id-table="assetHistory" data-search="true"
                                            data-side-pagination="server" data-show-columns="true"
                                            data-show-fullscreen="true" data-show-refresh="true" data-sort-order="desc"
                                            data-sort-name="created_at" data-show-export="true"
                                            data-export-options='{
                         "fileName": "export-asset-{{ $asset->id }}-history",
                         "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                       }'
                                            data-url="{{ route('api.activity.index', ['item_id' => $asset->id, 'item_type' => 'asset']) }}"
                                            data-cookie-id-table="assetHistory" data-cookie="true">
                                            <thead>
                                                <tr>
                                                    <th data-visible="true" data-field="icon" style="width: 40px;"
                                                        class="hidden-xs" data-formatter="iconFormatter">
                                                        {{ trans('admin/hardware/table.icon') }}</th>
                                                    <th class="col-sm-2" data-visible="true" data-field="action_date"
                                                        data-formatter="dateDisplayFormatter">{{ trans('general.date') }}
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true" data-field="admin"
                                                        data-formatter="usersLinkObjFormatter">
                                                        {{ trans('general.admin') }}
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true" data-field="action_type">
                                                        {{ trans('general.action') }}</th>
                                                    <th class="col-sm-2" data-visible="true" data-field="item"
                                                        data-formatter="polymorphicItemFormatter">
                                                        {{ trans('general.item') }}
                                                    </th>
                                                    <th class="col-sm-2" data-visible="true" data-field="target"
                                                        data-formatter="polymorphicItemFormatter">
                                                        {{ trans('general.target') }}</th>
                                                    <th class="col-sm-2" data-field="note">{{ trans('general.notes') }}
                                                    </th>
                                                    <th class="col-md-3" data-field="signature_file" data-visible="false"
                                                        data-formatter="imageFormatter">{{ trans('general.signature') }}
                                                    </th>
                                                    <th class="col-md-3" data-visible="false" data-field="file"
                                                        data-visible="false" data-formatter="fileUploadFormatter">
                                                        {{ trans('general.download') }}</th>
                                                    <th class="col-sm-2" data-field="log_meta" data-visible="true"
                                                        data-formatter="changeLogFormatter">
                                                        {{ trans('admin/hardware/table.changed') }}</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div> <!-- /.row -->
                            </div>
                        </div>
                    </div> <!-- /.tab-pane history -->

                    <div class="tab-pane fade" id="files">

                        <div class="row">
                            <div class="col-md-12">

                                @if ($asset->uploads->count() > 0)
                                    <table class="table table-striped snipe-table" id="assetFileHistory"
                                        data-pagination="true" data-id-table="assetFileHistory" data-search="true"
                                        data-side-pagination="client" data-sortable="true" data-show-columns="true"
                                        data-show-fullscreen="true" data-show-refresh="true" data-sort-order="desc"
                                        data-sort-name="created_at" data-show-export="true"
                                        data-export-options='{
                                   "fileName": "export-asset-{{ $asset->id }}-files",
                                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                       }'
                                        data-cookie-id-table="assetFileHistory">
                                        <thead>
                                            <tr>
                                                <th data-visible="true" data-field="icon" data-sortable="true">
                                                    {{ trans('general.file_type') }}</th>
                                                <th class="col-md-2" data-searchable="true" data-visible="true"
                                                    data-field="image">{{ trans('general.image') }}</th>
                                                <th class="col-md-2" data-searchable="true" data-visible="true"
                                                    data-field="filename" data-sortable="true">
                                                    {{ trans('general.file_name') }}</th>
                                                <th class="col-md-1" data-searchable="true" data-visible="true"
                                                    data-field="filesize">{{ trans('general.filesize') }}</th>
                                                <th class="col-md-2" data-searchable="true" data-visible="true"
                                                    data-field="notes" data-sortable="true">
                                                    {{ trans('general.notes') }}
                                                </th>
                                                <th class="col-md-1" data-searchable="true" data-visible="true"
                                                    data-field="download">{{ trans('general.download') }}</th>
                                                <th class="col-md-2" data-searchable="true" data-visible="true"
                                                    data-field="created_at" data-sortable="true">
                                                    {{ trans('general.created_at') }}</th>
                                                <th class="col-md-1" data-searchable="true" data-visible="true"
                                                    data-field="actions">{{ trans('table.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($asset->uploads as $file)
                                                <tr>
                                                    <td><i class="{{ Helper::filetype_icon($file->filename) }} icon-med"
                                                            aria-hidden="true"></i></td>
                                                    <td>
                                                        @if (Helper::checkUploadIsImage($file->get_src('assets')))
                                                            <a href="{{ route('show/assetfile', ['assetId' => $asset->id, 'fileId' => $file->id]) }}"
                                                                data-toggle="lightbox" data-type="image"
                                                                data-title="{{ $file->filename }}"
                                                                data-footer="{{ Helper::getFormattedDateObject($asset->last_checkout, 'datetime', false) }}">
                                                                <img src="{{ route('show/assetfile', ['assetId' => $asset->id, 'fileId' => $file->id]) }}"
                                                                    style="max-width: 50px;">
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (Storage::exists('private_uploads/assets/' . $file->filename))
                                                            {{ $file->filename }}
                                                        @else
                                                            <del>{{ $file->filename }}</del>
                                                        @endif
                                                    </td>
                                                    <td
                                                        data-value="{{ Storage::exists('private_uploads/assets/' . $file->filename) ? Storage::size('private_uploads/assets/' . $file->filename) : '' }}">
                                                        {{ @Helper::formatFilesizeUnits(Storage::exists('private_uploads/assets/' . $file->filename) ? Storage::size('private_uploads/assets/' . $file->filename) : '') }}
                                                    </td>
                                                    <td>
                                                        @if ($file->note)
                                                            {{ $file->note }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($file->filename && Storage::exists('private_uploads/assets/' . $file->filename))
                                                            <a href="{{ route('show/assetfile', [$asset->id, $file->id]) }}"
                                                                class="btn btn-default">
                                                                <i class="fas fa-download" aria-hidden="true"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($file->created_at)
                                                            {{ Helper::getFormattedDateObject($file->created_at, 'datetime', false) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @can('update', \App\Models\Asset::class)
                                                            <a class="btn delete-asset btn-sm btn-danger btn-sm"
                                                                href="{{ route('delete/assetfile', [$asset->id, $file->id]) }}"
                                                                data-tooltip="true" data-title="Delete"
                                                                data-content="{{ trans('general.delete_confirm', ['item' => $file->filename]) }}"><i
                                                                    class="fas fa-trash icon-white"
                                                                    aria-hidden="true"></i></a>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="alert alert-info alert-block">
                                        <i class="fas fa-info-circle"></i>
                                        {{ trans('general.no_results') }}
                                    </div>
                                @endif

                            </div> <!-- /.col-md-12 -->
                        </div> <!-- /.row -->

                    </div> <!-- /.tab-pane files -->

                    <div class="tab-pane fade" id="listimages" style="overflow: auto;">

                        <div class="row">
                            <div class="col-md-12">
                                <table id="tableMovement" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Assets</th>
                                            <th>Assets Tags</th>
                                            <th>Images</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($listImages as $index => $project)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $project->name }}</td>
                                                <td>{{ $project->asset_tag }}</td>
                                                <td><img src="{{ $project->getImageUrl() ? $project->getImageUrl() : null }}"
                                                        class="img-thumbnail" width="100" height="100"></td>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-sm-5">
                                                            <button type="button" class="btn btn-danger"
                                                                data-toggle="modal"
                                                                data-target="#modal-Delete{{ $project->id }}">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Modal Delete --}}
                                            <div class="modal fade" id="modal-Delete{{ $project->id }}">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Confirmation Delete Project</h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <div class="row">
                                                                <div class="col-sm-10">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal" aria-label="Close">
                                                                        Close
                                                                    </button>
                                                                </div>
                                                                <div class="col-sm-1 ml-auto">
                                                                    <!-- Added ml-auto class to align the button to the right -->
                                                                    <form
                                                                        action="{{ route('delete/assetimage', $project->id) }}"
                                                                        method="post">
                                                                        @csrf
                                                                        @method('delete')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            Delete
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!-- /.modal-content -->
                                                </div>
                                                <!-- /.modal-dialog -->
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    {{-- <div class="tab-pane fade" id="modelfiles">
                    <div class="row">
                        <div class="col-md-12">

                            @if ($asset->model->uploads->count() > 0)
                                <table class="table table-striped snipe-table" id="assetModelFileHistory"
                                    data-pagination="true" data-id-table="assetModelFileHistory" data-search="true"
                                    data-side-pagination="client" data-sortable="true" data-show-columns="true"
                                    data-show-fullscreen="true" data-show-refresh="true" data-sort-order="desc"
                                    data-sort-name="created_at" data-show-export="true"
                                    data-export-options='{
                         "fileName": "export-assetmodel-{{ $asset->model->id }}-files",
                         "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                       }'
                                    data-cookie-id-table="assetFileHistory">
                                    <thead>
                                        <tr>
                                            <th data-visible="true" data-field="icon" data-sortable="true">
                                                {{ trans('general.file_type') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="image">{{ trans('general.image') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="filename" data-sortable="true">
                                                {{ trans('general.file_name') }}</th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="filesize">{{ trans('general.filesize') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="notes" data-sortable="true">{{ trans('general.notes') }}
                                            </th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="download">{{ trans('general.download') }}</th>
                                            <th class="col-md-2" data-searchable="true" data-visible="true"
                                                data-field="created_at" data-sortable="true">
                                                {{ trans('general.created_at') }}</th>
                                            <th class="col-md-1" data-searchable="true" data-visible="true"
                                                data-field="actions">{{ trans('table.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($asset->model->uploads as $file)
                                            <tr>
                                                <td><i class="{{ Helper::filetype_icon($file->filename) }} icon-med"
                                                        aria-hidden="true"></i></td>
                                                <td>
                                                    @if (Helper::checkUploadIsImage($file->get_src('assetmodels')))
                                                        <a href="{{ route('show/modelfile', ['modelID' => $asset->model->id, 'fileId' => $file->id]) }}"
                                                            data-toggle="lightbox" data-type="image"
                                                            data-title="{{ $file->filename }}"
                                                            data-footer="{{ Helper::getFormattedDateObject($asset->last_checkout, 'datetime', false) }}">
                                                            <img src="{{ route('show/modelfile', ['modelID' => $asset->model->id, 'fileId' => $file->id]) }}"
                                                                style="max-width: 50px;">
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (Storage::exists('private_uploads/assetmodels/' . $file->filename))
                                                        {{ $file->filename }}
                                                    @else
                                                        <del>{{ $file->filename }}</del>
                                                    @endif
                                                </td>
                                                <td
                                                    data-value="{{ Storage::exists('private_uploads/assetmodels/' . $file->filename) ? Storage::size('private_uploads/assetmodels/' . $file->filename) : '' }}">
                                                    {{ Storage::exists('private_uploads/assetmodels/' . $file->filename) ? Helper::formatFilesizeUnits(Storage::size('private_uploads/assetmodels/' . $file->filename)) : '' }}
                                                </td>
                                                <td>
                                                    @if ($file->note)
                                                        {{ $file->note }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($file->filename && Storage::exists('private_uploads/assetmodels/' . $file->filename))
                                                        <a href="{{ route('show/modelfile', [$asset->model->id, $file->id]) }}"
                                                            class="btn btn-default">
                                                            <i class="fas fa-download" aria-hidden="true"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($file->created_at)
                                                        {{ Helper::getFormattedDateObject($file->created_at, 'datetime', false) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('update', \App\Models\AssetModel::class)
                                                        <a class="btn delete-asset btn-sm btn-danger btn-sm" href="{{ route('delete/modelfile', [$asset->model->id, $file->id]) }}" data-tooltip="true" data-title="Delete" data-content="{{ trans('general.delete_confirm', ['item' => $file->filename]) }}"><i class="fas fa-trash icon-white" aria-hidden="true"></i></a>
                                                          @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-info alert-block">
                                    <i class="fas fa-info-circle"></i>
                                    {{ trans('general.no_results') }}
                                </div>
                            @endif

                        </div> <!-- /.col-md-12 -->
                    </div> <!-- /.row -->
                </div>  --}}
                    <!-- /.tab-pane files -->
                </div>
            </div>
        </div> <!-- /. col-md-12 -->
    </div> <!-- /. row -->



    @can('update', \App\Models\Asset::class)
        @include ('modals.upload-file', ['item_type' => 'asset', 'item_id' => $asset->id])
    @endcan

@stop
@push('js')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.8.1/baguetteBox.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endpush
@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <script>
        // baguetteBox.run('.tz-gallery');
        $(document).ready(function() {
            $('#tableMovement').DataTable({
                // dom: 'Qlfrtip'
                responsive: true
            });
        });
    </script>

    <script>
        let slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            let dots = document.getElementsByClassName("demo");
            let captionText = document.getElementById("caption");
            if (n > slides.length) {
                slideIndex = 1
            }
            if (n < 1) {
                slideIndex = slides.length
            }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex - 1].style.display = "block";
            dots[slideIndex - 1].className += " active";
            captionText.innerHTML = dots[slideIndex - 1].alt;
        }
    </script>
@stop
