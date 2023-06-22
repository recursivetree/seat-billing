@extends('web::layouts.app')

@section('title', trans('billing::tax.user_tax_invoices'))
@section('page_header', trans('billing::tax.user_tax_invoices'))

@section('content')
    @include("treelib::giveaway")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Instructions</h3>
        </div>
        <div class="card-body">
            <p>
                On this page you have an overview of the tax you have to pay.
                There is a table for each corporation you owe taxes to, containing how much isk you have to pay and the payment status.
                You can pay your taxes by transferring the ISK listed under "remaining" to the corporation.
                Make sure to include the tax code in the description of the payment, or the payment can't be automatically detected.
                After you initiate the payment, it can take up to an hour until the payment status changes.
                This is due to how ESI work, please have some patience.
            </p>
        </div>
    </div>

    @if($invoices->isEmpty())
        <div class="alert alert-info">
            <h4 class="alert-heading">
                <i class="fas fa-info"></i> {{ trans('billing::billing.info') }}
            </h4>
            <p>
                {{ trans('billing::tax.no_user_invoice_history') }}
            </p>
        </div>
    @endif

    @foreach($invoices as $corp_invoices)
        <div class="card">
            <div class="card-header d-flex flex-row- align-items-baseline justify-content-between">
                <h3 class="card-title flex-grow-1">{{ trans('billing::tax.tax_to_corporation_title', ['corporation'=>$corp_invoices->first()->receiver_corporation->name ?? trans("web::seat.unknown")]) }}</h3>
                <form action="{{ route("tax.balanceUserOverpayment") }}" method="POST">
                    @csrf
                    <input type="hidden" name="corporation_id" value="{{$corp_invoices->first()->receiver_corporation_id}}">
                    <button type="submit" class="btn btn-primary">{{ trans('billing::tax.balance_overpayments') }}</button>
                </form>
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
                            <th>{{ trans('billing::tax.created_at') }}</th>
                            <th>{{ trans('billing::tax.due_until') }}</th>
                            <th>{{ trans('billing::tax.tax_code') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corp_invoices as $invoice)
                            <tr>
                                <td data-sort="{{$invoice->character_id}}">@include("web::partials.character",["character"=>$invoice->character])</td>
                                <td>@include("web::partials.corporation",["corporation"=>$invoice->receiver_corporation])</td>
                                <td>{{ trans($invoice->reason_translation_key, $invoice->reason_translation_data) }}</td>
                                <td data-sort="{{$invoice->amount - $invoice->paid}}">{{ number($invoice->amount - $invoice->paid, 0) }} ISK</td>
                                <td>@include("billing::tax.partials.invoiceStatus",compact("invoice"))</td>
                                <td data-sort="{{ $invoice->created_at->timestamp }}">{{ $invoice->created_at->format('M d Y') }}</td>
                                @if(in_array($invoice->state,["pending","open"]) && carbon($invoice->due_until) < $now)
                                    <td class="table-warning" data-sort="{{ carbon($invoice->due_until)->timestamp }}">{{ carbon($invoice->due_until)->format('M d Y') }}</td>
                                @else
                                    <td data-sort="{{ carbon($invoice->due_until)->timestamp }}">{{ carbon($invoice->due_until)->format('M d Y') }}</td>
                                @endif
                                <td>
                                    <code>{{ \Denngarr\Seat\Billing\Helpers\TaxCode::generateInvoiceCode($invoice->id) }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><b>{{ trans('billing::billing.total') }}:</b></td>
                            <td></td>
                            <td></td>
                            <td>{{ number($corp_invoices->sum("amount") - $corp_invoices->sum("paid"), 0) }} ISK</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <code>{{ \Denngarr\Seat\Billing\Helpers\TaxCode::generateCorporationCode($corp_invoices->first()->receiver_corporation_id) }}</code>
                            </td>
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

