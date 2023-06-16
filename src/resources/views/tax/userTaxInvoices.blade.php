@extends('web::layouts.app')

@section('title', trans('billing::tax.user_tax_invoices'))
@section('page_header', trans('billing::tax.user_tax_invoices'))

@section('content')
    @include("treelib::giveaway")

    @if($invoices->isEmpty())
        <div class="alert alert-info">
            <h4 class="alert-heading">
                <i class="fas fa-info"></i> {{ trans('billing::billing.info') }}
            </h4>
            <p>
                {{ trans('billing::tax.no_user_billing_history') }}
            </p>
        </div>
    @endif

    @foreach($invoices as $corp_invoices)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ trans('billing::tax.tax_to_corporation_title', ['corporation'=>$corp_invoices->first()->receiver_corporation()->name ?? trans("web::seat.unknown")]) }}</h3>
            </div>
            <div class="card-body">
                    <table class="table DataTable table-hover table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('billing::tax.character') }}</th>
                                <th>{{ trans('billing::tax.receiver_corporation') }}</th>
                                <th>{{ trans('billing::tax.tax_reason') }}</th>
                                <th>{{ trans('billing::tax.remaining_tax') }}</th>
                                <th>{{ trans('billing::tax.tax_state') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corp_invoices as $invoice)
                                <tr>
                                    <td data-sort="{{$invoice->character_id}}">@include("web::partials.character",["character"=>$invoice->character])</td>
                                    <td>@include("web::partials.corporation",["corporation"=>$invoice->receiver_corporation])</td>
                                    <td>{{ trans($invoice->reason_translation_key, $invoice->reason_translation_data) }}</td>
                                    <td data-sort="{{$invoice->amount - $invoice->paid}}">{{ number($invoice->amount - $invoice->paid, 0) }}</td>
                                    <td>@include("billing::tax.partials.invoiceStatus",compact("invoice"))</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>

                            </tr>
                        </tfoot>
                    </table>
            </div>
        </div>
    @endforeach
@endsection

@push('javascript')
    <script>
        $(".DataTable").DataTable();
    </script>
@endpush

