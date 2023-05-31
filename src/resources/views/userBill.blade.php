@extends('web::layouts.app')

@section('title', trans('billing::billing.user_bills'))
@section('page_header', trans('billing::billing.user_bills'))

@section('content')
    @include("treelib::giveaway")

    @if($months->isEmpty())
        <div class="alert alert-info">
            <h4 class="alert-heading">
                <i class="fas fa-info"></i> {{ trans('billing::billing.info') }}
            </h4>
            <p>
                {{ trans('billing::billing.no_user_tax_history') }}
            </p>
        </div>
    @endif

    @foreach($months as $month)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="bills{{$month->first()->year}}{{$month->first()->month}}">{{ date('Y-M', mktime(0,0,0, $month->first()->month, 1, $month->first()->year)) }}</h3>
            </div>
            <div class="card-body">
                    <table class="table DataTable table-hover table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('billing::billing.character') }}</th>
                                <th>{{ trans('billing::billing.corporation') }}</th>
                                <th>{{ trans('billing::billing.mining_amount') }}</th>
                                <th>{{ trans('billing::billing.mining_tax') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($month as $bill)
                                <tr>
                                    <td>@include("web::partials.character",["character"=>$bill->character])</td>
                                    <td>@include("web::partials.corporation",["corporation"=>$bill->corporation])</td>
                                    <td data-sort="{{$bill->mining_total}}">{{ number($bill->mining_total, 0) }} {{ trans('billing::billing.isk') }}</td>
                                    <td data-sort="{{$bill->mining_tax}}">{{ number($bill->mining_tax, 0) }} {{ trans('billing::billing.isk') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><b>{{ trans('billing::billing.total') }}:</b> {{ $month->sum("mining_total") }} {{ trans('billing::billing.isk') }}</td>
                                <td><b>{{ trans('billing::billing.total') }}:</b> {{ $month->sum("mining_tax") }} {{ trans('billing::billing.isk') }}</td>
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

