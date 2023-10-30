@extends('web::layouts.app')

@section('title', trans('billing::tax.user_tax_invoices'))
@section('page_header', trans('billing::tax.user_tax_invoices'))

@section('content')
    @include("treelib::giveaway")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('billing::tax.instructions') }}</h3>
        </div>
        <div class="card-body">
            <h6>{{ trans('billing::tax.instructions') }}</h6>
            <p>
                {{ trans('billing::tax.tax_instructions') }}
            </p>
            <h6> {{ trans('billing::tax.tax_states') }}</h6>
            <ul>
                <li><span class="badge badge-warning">{{ trans('billing::tax.tax_state_open') }}</span> {{ trans('billing::tax.tax_state_open_desc') }}</li>
                <li><span class="badge badge-secondary">{{ trans('billing::tax.tax_state_pending') }}</span> {{ trans('billing::tax.tax_state_pending_desc') }}</li>
                <li><span class="badge badge-success">{{ trans('billing::tax.tax_state_completed') }}</span> {{ trans('billing::tax.tax_state_completed_desc') }}</li>
                <li><span class="badge badge-success">{{ trans('billing::tax.tax_state_prediction') }}</span> {!! trans('billing::tax.tax_state_prediction_desc') !!}</li>
                <li><span class="badge badge-danger">{{ trans('billing::tax.tax_state_overtaxed') }}</span> {!! trans('billing::tax.tax_state_overtaxed_desc',['button'=>trans('billing::tax.balance_overpayments')]) !!}</li>

            </ul>
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
                                    @if(!in_array($invoice->state,['prediction','completed']) || \Denngarr\Seat\Billing\BillingSettings::$ALWAYS_SHOW_TAX_CODES->get(false))
                                        <code>{{ \Denngarr\Seat\Billing\Helpers\TaxCode::generateInvoiceCode($invoice->id) }}</code>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><b>{{ trans('billing::billing.total') }}:</b></td>
                            <td></td>
                            <td></td>
                            <td>
                               @php($total = $corp_invoices->where("state","!==","prediction")->sum("amount") - $corp_invoices->where("state","!==","prediction")->sum("paid"))
                                {{ number($total, 0) }} ISK
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                @if($total > 0 || \Denngarr\Seat\Billing\BillingSettings::$ALWAYS_SHOW_TAX_CODES->get(false))
                                    <code>{{ \Denngarr\Seat\Billing\Helpers\TaxCode::generateCorporationCode($corp_invoices->first()->receiver_corporation_id) }}</code>
                                @endif
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

