@extends('web::layouts.app')

@section('title', trans('billing::billing.user_bills'))
@section('page_header', trans('billing::billing.user_bills'))

@section('content')
    @include("treelib::giveaway")

    @if($months->isEmpty())
        <div class="alert alert-info">
            <h4 class="alert-heading">
                <i class="fas fa-info"></i> Info
            </h4>
            <p>
                It looks like there is no mining tax history available for you.
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
                                <th>Character</th>
                                <th>Corporation</th>
                                <th>Mining Amount</th>
                                <th>Mining Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($month as $bill)
                                <tr>
                                    <td>@include("web::partials.character",["character"=>$bill->character])</td>
                                    <td>@include("web::partials.corporation",["corporation"=>$bill->corporation])</td>
                                    <td data-sort="{{$bill->mining_total}}">{{ number($bill->mining_total, 0) }} ISK</td>
                                    <td data-sort="{{$bill->mining_tax}}">{{ number($bill->mining_tax, 0) }} ISK</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><b>Total:</b> {{ $month->sum("mining_total") }} ISK</td>
                                <td><b>Total:</b> {{ $month->sum("mining_tax") }} ISK</td>
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

