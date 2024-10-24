@extends('layouts.edit-form', [
    'createText' => 'Create ' . trans('general.project_movement'),
    'updateText' => 'Update ' . trans('general.project_movement'),
    'formAction' => $item->id ? route('project-movement.update', $item->id) : route('project-movement.store'),
])

@push('css')
    <style>

    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
@section('inputFields')
    <div class="form-group {{ $errors->has('project_number') ? ' has-error' : '' }}">
        <label for="project_number" class="col-md-3 control-label">{{ trans('admin/movement/form.project_number') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <input class="form-control" type="text" name="project_number" aria-label="project_number" id="project_number"
                value="{{ $item->id ? $item->project_number : old('project_number') }}" placeholder="ex: A2B-SB3" required
                maxlength="150" oninput="checkInputFormProjectNumber()" />
            {!! $errors->first(
                'project_number',
                '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
            ) !!}
            <span class="alert-msg" style="color: rgb(0, 174, 255);display: none;" aria-hidden="true"
                id="w-project-number">Information: Max length for project number is 150
                letters including symbol & without
                space</span>
        </div>
    </div>
    <div class="form-group {{ $errors->has('project_name') ? ' has-error' : '' }}">
        <label for="project_name" class="col-md-3 control-label">{{ trans('admin/movement/form.project_name') }}</label>
        <div class="col-md-7 col-sm-12 required">
            <input class="form-control" type="text" name="project_name" aria-label="project_name" id="project_name"
                value="{{ $item->id ? $item->project_name : old('project_name') }}" required
                oninput="checkInputFormProjectName()" maxlength="150" />
            {!! $errors->first(
                'project_name',
                '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
            ) !!}
            <span class="alert-msg" id="w-project-name" style="color:  rgb(0, 174, 255);display:none;"
                aria-hidden="true">Information: Max
                length for project number is 150
                letters including symbol</span>
        </div>
    </div>


    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
        <label for="address" class="col-md-3 control-label">{{ trans('admin/movement/form.address') }}</label>
        <div class="col-md-7 col-sm-12 {{ $item->id ? '' : 'required' }}">
            <textarea class="form-control"name="address[]" aria-label="address" id="address" {{ $item->id ? '' : 'required' }}>{{ old('address') }}</textarea>
            {!! $errors->first(
                'address',
                '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
            ) !!}
        </div>
        <div class="col-md-1 col-sm-1 text-left">
            <button class="btn btn-sm btn-secondary" type="button" id="addNewInput"><i class="fa fa-add"></i></button>
        </div>
    </div>

    <div id="newAddress"></div>
@endsection

@section('outside-box')
    <div class="box box-default">
        <div class="box-body" style="overflow: auto;">
            @if ($item->id)
                <table id="tableAddress" class="display nowrap" style="width:100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($address as $index => $addrs)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $addrs->address }}</td>
                                <td>
                                    <div class="row">
                                        <span data-tooltip="true" title="Edit address"> <button type="button"
                                                class="btn btn-sm btn-warning" data-toggle="modal"
                                                data-target="#modal-Updated{{ $addrs->id }}">
                                                <i class="fa fa-edit"></i>
                                            </button></span>
                                        <span data-tooltip="true" title="Delete address"> <button type="button"
                                                class="btn btn-sm btn-danger" data-toggle="modal"
                                                data-target="#modal-Delete{{ $addrs->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button></span>
                                    </div>
                                </td>
                            </tr>
                            {{-- Modal Update --}}
                            <div class="modal fade" id="modal-Updated{{ $addrs->id }}">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Update Address</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <!-- Added ml-auto class to align the button to the right -->
                                        <form action="{{ route('project-movement.updateAddress', $addrs->id) }}"
                                            method="post">
                                            @csrf
                                            @method('put')
                                            <div class="modal-body">
                                                <div class="row required p-3">
                                                    <div class="col-md-3"><strong>Address</strong></div>
                                                    <div class="col-md-9">
                                                        <textarea class="form-control" name="addressUpdate" aria-label="addressUpdate" id="addressUpdate" required>{{ old('addressUpdate') ?? $addrs->address }}</textarea>
                                                        {!! $errors->first(
                                                            'addressUpdate',
                                                            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
                                                        ) !!}
                                                    </div>
                                                </div>

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
                                                        <button type="submit" class="btn btn-danger">
                                                            Update
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>
                            {{-- Modal Delete --}}
                            <div class="modal fade" id="modal-Delete{{ $addrs->id }}">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Confirmation Delete Address</h4>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3"><strong>Address</strong></div>
                                                <div class="col-md-9">: {{ $addrs->address }}</div>
                                            </div>
                                            <br>
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
                                                        action="{{ route('project-movement.deleteAddress', $addrs->id) }}"
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
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endpush

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        function checkInputFormProjectNumber() {
            var inputField = document.getElementById('project_number');
            var warning = document.getElementById('w-project-number');
            inputField.value = inputField.value.toUpperCase();
            if (inputField.value !== '') {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }

        function checkInputFormProjectName() {
            var inputField = document.getElementById('project_name');
            var warning = document.getElementById('w-project-name');
            if (inputField.value !== '') {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }
        $(document).ready(function() {
            $('.select2').select2();
            $('#tableAddress').DataTable({
                // dom: 'Qlfrtip'
                responsive: true
            });
            $('#project_number').on('input', function() {
                var inputValue = $(this).val();
                if (/\s/.test(inputValue)) {
                    alert('Project number cannot contain spaces.');
                    $(this).val(inputValue.replace(/\s+/g, ''));
                }
            });
            $('#addNewInput').click(function() {
                var newRow = '<div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">' +
                    '<label for="address" class="col-md-3 control-label">{{ trans('admin/movement/form.address') }}</label>' +
                    '<div class="col-md-7 col-sm-12 required">' +
                    '<textarea class="form-control" type="text" name="address[]" aria-label="address" id="address" required></textarea>' +
                    '</div>' +
                    '<div class="col-md-1 col-sm-1 text-left">' +
                    '<button class="btn btn-sm btn-danger deleteInput" type="button"><i class="fa fa-minus"></i></button>' +
                    '</div>' +
                    '</div>';
                $('#newAddress').append(newRow);
            });

            // Use event delegation to handle the click event for dynamically added delete buttons
            $('#newAddress').on('click', '.deleteInput', function() {
                $(this).closest('.form-group').remove();
            });
        });
    </script>
@endsection
