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
        {{-- {{ trans('general.all') }} --}}
    @endif
    {{ trans('general.project') }}

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
    @can('create', \App\Models\ProjectMovement::class)
        <a href="{{ route('project-movement.create') }}" accesskey="n" class="btn btn-primary pull-right"></i>
            {{ trans('general.create') }}</a>
    @endcan
@endsection

@section('content')
    <div class="container-fluid">
        <div class="box">
            <div class="box-body" style="overflow: auto;">
                <table id="tableMovement" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <th>Addresses</th>
                            <th>Movements Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectMovement as $index => $project)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ strtoupper($project->project_number) }}</td>
                                <td>{{ $project->project_name }}</td>
                                <td>
                                    @foreach (explode(',', $project->addresses) as $address)
                                        <ul class="list-group">
                                            <li>{{ $address }}</li>
                                        </ul>
                                    @endforeach
                                </td>
                                <td>{{ $movement->where('project_id', $project->id_project)->count() }}</td>
                                <td>

                                    <span data-tooltip="true" title="Edit project movement">
                                        <a href="{{ route('project-movement.edit', $project->id_project) }}"
                                            class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a></span>
                                    <span data-tooltip="true"
                                        title="{{ $movement->where('project_id', $project->id_project)->count() > 0 ? 'Cannot be deleted' : 'Delete project movement' }}">
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#modal-Delete{{ $project->id_project }}"
                                            {{ $movement->where('project_id', $project->id_project)->count() > 0 ? 'disabled' : '' }}>
                                            <i class="fa fa-trash"></i>
                                        </button></span>

                                </td>
                            </tr>

                            {{-- Modal Delete --}}
                            <div class="modal fade" id="modal-Delete{{ $project->id_project }}">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Confirmation Delete Project</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3"><strong>Project Number</strong></div>
                                                <div class="col-md-9">: {{ $project->project_number }}</div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3"><strong>Project Name</strong></div>
                                                <div class="col-md-9">: {{ $project->project_name }}</div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3"><strong>Addresses:</strong></div>
                                                <div class="col-md-9">
                                                    <ul class="list-group">
                                                        @foreach (explode(',', $project->addresses) as $address)
                                                            <li class="list-group-item">{{ $address }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
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
                                                    <form
                                                        action="{{ route('project-movement.destroy', $project->id_project) }}"
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
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <!-- <th>Person In Charge</th> -->
                            <th>Addresses</th>
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
            $('#tableMovement').DataTable({
                // dom: 'Qlfrtip'
                responsive: true
            });
        });
    </script>
    @include('partials.bootstrap-table')
@endsection
