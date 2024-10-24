{{-- <div class="form-group">
    <label for="" class="col-md-3 control-label">Status</label>
    <div class="col-md-7 required">
        <select name="status_movement" id="status_movement" class="form-control select2" style="width: 100%;" required>
            <option value="">Select Status</option>
            @foreach (\App\Models\MovementStatus::all() as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
        </select>
        {!! $errors->first(
            'status_movement',
            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
        ) !!}
    </div>
</div> --}}
