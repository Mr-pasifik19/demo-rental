@extends('layouts.default')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('title0')
    @if (Request::get('company_id') && $company)
        {{ $company->name }}
    @endif

    @if (Request::get('status'))
        @if (Request::get('status') == 'Pending')
            {{ trans('general.pending') }}
        @elseif (Request::get('status') == 'RTD')
            {{ trans('general.ready_to_deploy') }}
        @elseif (Request::get('status') == 'Deployed')
            {{ trans('general.deployed') }}
        @elseif (Request::get('status') == 'Undeployable')
            {{ trans('general.undeployable') }}
        @elseif (Request::get('status') == 'Deployable')
            {{ trans('general.ready_to_deploy') }}
        @elseif (Request::get('status') == 'Requestable')
            {{ trans('admin/hardware/general.requestable') }}
        @elseif (Request::get('status') == 'Archived')
            {{ trans('general.archived') }}
        @elseif (Request::get('status') == 'Deleted')
            {{ trans('general.deleted') }}
        @elseif (Request::get('status') == 'byod')
            {{ trans('general.byod') }}
        @endif
    @else
        {{ trans('general.all') }}
    @endif
    {{ trans('general.company_movement') }}

    @if (Request::has('order_number'))
        : Order #{{ Request::get('order_number') }}
    @endif
@endsection

@section('title')
    @yield('title0') @parent
@endsection
@section('header_right')
    {{-- <a href="{{ route('reports/custom') }}" style="margin-right: 5px;" class="btn btn-default">
        {{ trans('admin/movement/general.custom_export') }}</a> --}}
    @can('create', \App\Models\BranchCompany::class)
        <a href="{{ route('company-movement.create') }}" accesskey="n" class="btn btn-primary pull-right"></i>
            {{ trans('general.create') }}</a>
    @endcan
@endsection

@section('content')
    <div class="container-fluid">
        <div class="box">
            <div class="box-body" style="overflow: auto;">
                <table id="tableCompany" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company Branch</th>
                            <th>Address</th>
                            <th>Movements Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companyBranch as $index => $company)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><a href="{{ route('company-movement.showAssingnedMovement', $company->id) }}"
                                        data-tooltip="true"
                                        title="Shows the movements that have been assigned to the company branch">{{ $company->company_name }}</a>
                                </td>
                                <td>{{ $company->address }}</td>
                                <td>{{ $movement->where('company_id', $company->id)->count() }}</td>
                                <td>
                                    <span data-tooltip="true" title="Edit company movement"> <a
                                            href="{{ route('company-movement.edit', $company->id) }}"
                                            class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a></span>
                                    <span data-tooltip="true"
                                        title="{{ $movement->where('company_id', $company->id)->count() > 0 ? 'Cannot be deleted' : 'Delete company movement' }}"><button
                                            type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#modal-Delete{{ $company->id }}"
                                            {{ $movement->where('company_id', $company->id)->count() > 0 ? 'disabled' : '' }}>
                                            <i class="fa fa-trash"></i>
                                        </button></span>
                                </td>
                            </tr>

                            {{-- Modal Delete --}}
                            <div class="modal fade" id="modal-Delete{{ $company->id }}">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Confirmation Delete Company</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3"><strong>Company Name</strong></div>
                                                <div class="col-md-9">: {{ $company->company_name }}</div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3"><strong>Address</strong></div>
                                                <div class="col-md-9">: {{ $company->address }}</div>
                                            </div>
                                        </div>


                                        <div class="modal-footer">
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                                        aria-label="Close">
                                                        Close
                                                    </button>
                                                </div>
                                                <div class="col-sm-1 ml-auto">
                                                    <!-- Added ml-auto class to align the button to the right -->
                                                    <form action="{{ route('company-movement.destroy', $company->id) }}"
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
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Company Branch</th>
                            <th>Address</th>
                            <th>Movements Total</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endpush
@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function() {
            $('#tableCompany').DataTable({
                // dom: 'Qlfrtip'
                responsive: true
            });
        });
    </script>
    @include('partials.bootstrap-table')
@endsection
