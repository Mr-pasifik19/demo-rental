<!-- to_receiver -->
<div class="form-group {{ $errors->has('to_receiver') ? ' has-error' : '' }}">
    <label for="to_receiver" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7 col-sm-12{{ Helper::checkIfRequired($item, 'to_receiver') ? ' required' : '' }}">
        <input class="form-control" type="text" name="to_receiver" aria-label="to_receiver" id="to_receiver"
            value="{{ old('to_receiver', $item->to_receiver) }}"{!! Helper::checkIfRequired($item, 'name') ? ' data-validation="required" required' : '' !!} />
        {!! $errors->first(
            'to_receiver',
            '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>',
        ) !!}
    </div>
</div>
