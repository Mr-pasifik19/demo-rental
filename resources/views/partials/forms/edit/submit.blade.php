<!-- partials/forms/edit/submit.blade.php -->

<div class="box-footer text-right" style="padding-bottom: 0px;">
    <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>


    {{-- btn printed dibawah --}}

    {{-- <a href="{{ route('movement.invoice') }}" target="_blank" id="print-button" class="btn btn-white bg-green" @if (!Request::is('movement*')) style="display:none;" @endif>Print Commercial Invoices</a> --}}
    <button type="button" accesskey="ss" target="_blank" class="btn btn-white bg-green" id="print-button"
        @if (!Request::is('movement*')) style="display:none;" @endif><i class="fas fa-print icon-white"
            aria-hidden="true"></i>
        Print Commercial Invoices</button>

    <a href="{{ asset('uploads/file/CBP Form 4455.pdf') }}" class="btn btn-info"
        @if (!Request::is('movement*')) style="display:none;" @endif target="_blank"><i class="fas fa-download icon-white"></i>
        Download CBP</a>

    <button type="submit" accesskey="s" class="btn btn-primary" id="save-button"><i class="fas fa-check icon-white"
            aria-hidden="true"></i>
        {{ trans('general.save') }}</button>
    {{-- <!-- <a href="{{ route('movement.invoice') }}" target="_blank" id="print-button" class="btn btn-white bg-green"
        @if (!Request::is('movement*')) style="display:none;" @endif>Print Commercial Invoices</a> -->
  <button type="submit" accesskey="ss" target="_blank" class="btn btn-white bg-green"id="print-button"><i class="fas fa-print icon-white" aria-hidden="true"></i>
    Print Commercial Invoices</button> --}}


</div>
<!-- / partials/forms/edit/submit.blade.php -->
