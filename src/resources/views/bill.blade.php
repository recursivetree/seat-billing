@extends('web::layouts.grids.12')

@section('title', trans('billing::billing.pastbill'))
@section('page_header', trans('billing::billing.pastbill'))

@section('full')
    @include("treelib::giveaway")

    <input type="hidden" id="year" value="{{ $year }}">
    <input type="hidden" id="month" value="{{ $month }}">

    <ul class="nav nav-pills pb-3">
        <li class="nav-item"><a class="nav-link" href="#tab3"
                                data-toggle="tab">{{ trans('billing::billing.summary-ind-mining') }}</a></li>
        <li class="nav-item"><a class="nav-link" href="#tab2"
                                data-toggle="tab">{{ trans('billing::billing.summary-corp-pve') }}</a></li>
        <li class="nav-item"><a class="nav-link active" href="#tab1"
                                data-toggle="tab">{{ trans('billing::billing.summary-corp-mining') }}</a></li>
        <li class="nav-item"><a href="#tab4" data-toggle="tab" class="nav-link"><i class="fa fa-history"></i> Previous Bills</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tab1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('billing::billing.summary-corp-mining') }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="livenumbers">
                        <thead>
                            <tr>
                                <th>Corporation</th>
                                <th>Mined Amount (adjusted)</th>
                                <th>Tax Owed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats as $row)
                                <tr>
                                    <td>{{ $row->corporation->name }}</td>
                                    <td>{{ number_format($row->mining_total, 2) }}</td>
                                    <td>{{ number_format($row->mining_tax) }}</td>
                                </tr>
                            @endforeach
                            @if($stats->isEmpty())
                                <tr>
                                    <td colspan="3">
                                        There is nothing to show here. Please make sure to schedule the <code>billing:update</code> job on a <b>daily</b> schedule to get daily updates. You can also maually trigger an update by running <code>php artisan billing:update</code>.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('billing::billing.summary-corp-pve') }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="livepve">
                        <thead>
                            <tr>
                                <th>Corporation</th>
                                <th>Total Bounties</th>
                                <th>Tax Owed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats as $row)
                                <tr>

                                    <td>{{ $row->corporation->name }}</td>
                                    <td>{{ number_format($row->pve_total, 2) }}</td>
                                    <td>{{ number_format($row->pve_tax,2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tab3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('billing::billing.summary-ind-mining') }}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <select class="form-control" id="corpspinner">
                            <option disabled selected value="0">Please Choose a Corp</option>
                            @foreach($stats as $row)
                                <option value="{{ $row->corporation->corporation_id }}">{{ $row->corporation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <table class="table table-striped"
                           id='indivmining'>
                        <thead>
                        <tr>
                            <th>Character Name</th>
                            <th>Mining Amount</th>
                            <th>Tax Due</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tab4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('billing::billing.previousbill') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <a href="{{ route('billing.view') }}"
                           class="list-group-item list-group-item-action">
                            Current
                        </a>
                        @foreach($dates->chunk(3) as $date)
                            @foreach ($date as $yearmonth)
                                <a href="{{ route('billing.pastbilling', ['year' => $yearmonth['year'], 'month' => $yearmonth['month']]) }}"
                                   class="list-group-item list-group-item-action">
                                    {{ date('Y-M', mktime(0,0,0, $yearmonth['month'], 1, $yearmonth['year'])) }}
                                </a>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

@endsection

@push('javascript')
    @include('web::includes.javascript.id-to-name')

    <script type="application/javascript">

        table = $('#indivmining').DataTable({
        });

        ids_to_names();

        $('#corpspinner').change(function () {

            $('#indivmining').find('tbody').empty();
            id = $('#corpspinner').find(":selected").val();
            year = $('#year').val();
            month = $('#month').val();

            if (id > 0) {
                $.ajax({
                    headers: function () {
                    },
                    url: "/billing/character/" + id + "/" + year + "/" + month,
                    type: "GET",
                    dataType: 'json',
                    timeout: 10000
                }).done(function (result) {
                    if (result) {
                        table.clear();
                        for (var chars in result) {
                            const name = result[chars].character.name || "{{ trans('web::seat.unknown') }}"
                            table.row.add(['<a href="/characters/'+ result[chars].character_id +'/mining-ledger">'+name+'</a>', new Intl.NumberFormat('en-US').format(result[chars].mining_total)+ " ISK", new Intl.NumberFormat('en-US').format(result[chars].mining_tax)+" ISK"]);
                        }
                        table.draw();
                        ids_to_names();
                    }
                });
            }
        });

        $(document).ready( function () {
            $('#livenumbers').DataTable();
            $('#livepve').DataTable();
        } );
    </script>
@endpush
