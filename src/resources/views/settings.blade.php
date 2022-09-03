@extends('web::layouts.grids.6-6')

@section('title', trans('billing::billing.settings'))
@section('page_header', trans('billing::billing.settings'))

@section('left')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('billing::billing.settings') }}</h3>
        </div>
        <form method="POST" action="{{ route('billing.savesettings')  }}" class="form-horizontal">
            <div class="card-body">
                @csrf

                <h4>Basic Settings</h4>

                <div class="form-group">
                    <label for="oremodifier">Ore value modifier</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="oremodifier" id="oremodifier"
                               value="{{ setting('oremodifier', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="oretaxrate">Ore TAX Rate</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="oretaxrate" id="oretaxrate" size="4"
                               value="{{ setting('oretaxrate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>


                <div class="form-group">
                    <label for="refinerate">Ore Refining Rate</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="refinerate" id="refinerate" size="4"
                               value="{{ setting('refinerate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bountytaxrate">Bounty TAX Rate</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="bountytaxrate" id="bountytaxrate"
                               value="{{ setting('bountytaxrate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <hr/>

                <h4>Incentivized Settings</h4>

                <div class="form-group">
                    <label for="ioremodifier">Ore value modifier</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="ioremodifier" id="ioremodifier"
                               value="{{ setting('ioremodifier', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ioretaxrate">Ore TAX Rate</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="ioretaxrate" id="ioretaxrate"
                               value="{{ setting('ioretaxrate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ibountytaxrate">Bounty TAX Rate</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="ibountytaxrate" id="ibountytaxrate"
                               value="{{ setting('ibountytaxrate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="irate">Rates Threshold</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="irate" id="irate"
                               value="{{ setting('irate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <hr/>
                <h4>Valuation of Ore</h4>

                <div class="form-group">
                    @if (setting('pricevalue', true) == "m")
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue1" value="o">
                            <label class="form-check-label" for="pricevalue1">
                                Value at Ore Price
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue2" value="m" checked>
                            <label class="form-check-label" for="pricevalue2">
                                Value at Mineral Price
                            </label>
                        </div>
                    @else
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue1" value="o" checked>
                            <label class="form-check-label" for="pricevalue1">
                                Value at Ore Price
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue2" value="m">
                            <label class="form-check-label" for="pricevalue2">
                                Value at Mineral Price
                            </label>
                        </div>
                    @endif
                </div>


            </div>

            <div class="card-footer">
                <input class="btn btn-success pull-right" type="submit" value="Update">
            </div>
        </form>

    </div>
@endsection

@section('right')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('billing::billing.settings') }}</h3>
        </div>
        <div class="card-body">
            <div class="col-sm-12">
                <p><label>Ore value modifier:</label> This is a modifier used on the base costs of the ore/minerals/goo
                    to adjust for inflation/deflation during the billing period. Normally this is 90-95% </p>
            </div>
            <div class="col-sm-12">
                <p><label>Ore Tax Rate:</label> Rate to tax on value of the mined materials. </p>
            </div>
            <div class="col-sm-12">
                <p><label>Ore Refining Rate:</label> This should be the max refine amount in your area. Max rates with
                    RX-804 implant, level V skills, and a T2 Rigged Tatara is 89.4%. Adjust this as you see fit, but I
                    recommend using the maximum rate available to your members in your area of space.</p>
            </div>
            <div class="col-sm-12">
                <p><label>Bounty Tax Rate:</label> Rate of ratting bounties to tax. Usually 5-10%</p>
            </div>
            <div class="col-sm-12">
                <p><label>Incentivised Rates:</label> Incentivised rates are on a per-corporation basis only. These are
                    discounted rates based on the number of members in the corp have signed up on Seat, including all
                    alts. If they're not signed up on SeAT, the alliance is not seeing their mining amounts, therefore,
                    they get higher tax rates.</p>
            </div>
            <div class="col-sm-12">
                <p><label>Valuation of Ore:</label> Value of ore can be determined with two methods: By ore type OR By
                    mineral content. If you are moon mining, it's better to use mineral content as it's more accurate as
                    Moon Goo is rarely sold by the raw ore, but more often as refined products. This keeps the moon
                    mining honest.</p>
            </div>
        </div>
    </div>
@endsection
