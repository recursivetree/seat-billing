@extends('web::layouts.grids.6-6')

@section('title', trans('billing::billing.settings'))
@section('page_header', trans('billing::billing.settings'))

@section('left')
    @include("treelib::giveaway")

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

                <hr>

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
                    <label for="ioretaxmodifier">Ore Tax Modifier</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="ioretaxmodifier" id="ioretaxmodifier"
                               value="{{ setting('ioretaxmodifier', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ibountytaxmodifier">Bounty Tax Modifier</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="ibountytaxmodifier" id="ibountytaxmodifier"
                               value="{{ setting('ibountytaxmodifier', true) }}"/>
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
                    <label>Valuation Mode</label>
                    @if (setting('pricevalue', true) == "m")
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue1" value="o">
                            <label class="form-check-label" for="pricevalue1">
                                Value at Ore Price
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue2" value="m"
                                   checked>
                            <label class="form-check-label" for="pricevalue2">
                                Value at Mineral Price
                            </label>
                        </div>
                    @else
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricevalue" id="pricevalue1" value="o"
                                   checked>
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

                <div class="form-group">
                    <label>Price Source</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pricesource" id="pricesource1" value="sell_price" @checked(setting("price_source", true)==="sell_price")>
                        <label class="form-check-label" for="pricesource1">
                            Value at Sell Price
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pricesource" id="pricesource2" value="buy_price" @checked(setting("price_source", true)==="buy_price")>
                        <label class="form-check-label" for="pricesource2">
                            Value at Buy Price
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pricesource" id="pricesource3" value="adjusted_price" @checked(setting("price_source", true)==="adjusted_price")>
                        <label class="form-check-label" for="pricesource3">
                            Value at CCP Price
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pricesource" id="pricesource4" value="average_price" @checked(setting("price_source", true)==="average_price")>
                        <label class="form-check-label" for="pricesource4">
                            Value at CCP Market Price (old default)
                        </label>
                    </div>
                </div>

                <hr>

                <h4>Ore Tax</h4>

                <div class="form-group">
                    <label for="r64taxmodifier">R64 Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="r64taxmodifier" id="r64taxmodifier"
                               value="{{ $ore_tax->firstWhere("group_id",1923)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="r32taxmodifier">R32 Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="r32taxmodifier" id="r32taxmodifier"
                               value="{{ $ore_tax->firstWhere("group_id",1922)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="r16taxmodifier">R16 Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="r16taxmodifier" id="r16taxmodifier"
                               value="{{ $ore_tax->firstWhere("group_id",1921)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="r8taxmodifier">R8 Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="r8taxmodifier" id="r8taxmodifier"
                               value="{{ $ore_tax->firstWhere("group_id",1920)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="r4taxmodifier">R4 Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="r4taxmodifier" id="r4taxmodifier"
                               value="{{ $ore_tax->firstWhere("group_id",1884)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gastax">Gas Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="gastax" id="gastax"
                               value="{{ $ore_tax->firstWhere("group_id",711)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="icetax">Ice Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="icetax" id="icetax"
                               value="{{ $ore_tax->firstWhere("group_id",465)->tax_rate ?? 0}}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="oretaxrate">Other Ores Tax</label>
                    <div class="d-flex flex-row align-items-baseline">
                        <input class="form-control" type="number" name="oretaxrate" id="oretaxrate" size="4"
                               value="{{ setting('oretaxrate', true) }}"/>
                        <div class="pl-2">%</div>
                    </div>
                </div>

                <hr>
                <h4>{{ trans("billing::billing.tax_invoices") }}</h4>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tax_invoices" id="tax_invoices1" value="enabled" @checked(\Denngarr\Seat\Billing\BillingSettings::$GENERATE_TAX_INVOICES->get(false)===true)>
                    <label class="form-check-label" for="tax_invoices1">
                        Enable Tax Invoices
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tax_invoices" id="tax_invoices2" value="disabled" @checked(\Denngarr\Seat\Billing\BillingSettings::$GENERATE_TAX_INVOICES->get(false)===false)>
                    <label class="form-check-label" for="tax_invoices2">
                        Disable Tax Invoices
                    </label>
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
                <label>Ore value modifier:</label>
                <p>
                    This is a modifier used on the base costs of the ore/minerals/goo
                    to adjust for inflation/deflation during the billing period. Normally this is 90-95%
                </p>
            </div>
            <div class="col-sm-12">
                <label>Ore Refining Rate:</label>
                <p>
                    This should be the max refine amount in your area. Max rates with
                    RX-804 implant, level V skills, and a T2 Rigged Tatara is 89.4%. Adjust this as you see fit, but I
                    recommend using the maximum rate available to your members in your area of space.
                </p>
            </div>
            <div class="col-sm-12">
                <label>Bounty Tax Rate:</label>
                <p>
                    Rate of ratting bounties to tax. Usually 5-10%
                </p>
            </div>
            <div class="col-sm-12">
                <label>Incentivised Settings:</label>
                <p>Incentivised modifiers are on a per-corporation basis only.
                    These are modifiers applied to corps where at least a certain number of members (including alts)
                    have registered on SeAT. If they're not signed up on SeAT, the alliance is not seeing their mining
                    amounts and missing on tax, therefore the corporation gets higher tax rates.
                </p>
            </div>
            <div class="col-sm-12">
                <label>Incentivised Ore Value Modifier:</label>
                <p>
                    Ore Value Modifier to use for corps with incentivised rates.
                </p>
            </div>
            <div class="col-sm-12">
                <label>Incentivised Ore Tax Modifier:</label>
                <p>
                    This modifier is applied to the normal tax. With your normal tax at 5 % and the incentivised ore tax
                    modifier at 50%, your members will have to pay 2.5% tax
                </p>
            </div>
            <div class="col-sm-12">
                <label>Incentivised Bounty Tax Modifier:</label>
                <p>
                    This modifier is applied to the normal tax. With your normal tax at 5 % and the incentivised ore tax
                    modifier at 50%, your members will have to pay 2.5% tax
                </p>
            </div>
            <div class="col-sm-12">
                <label>Rates Threshold:</label>
                <p>
                    When more than x% of the members of a corp are registered on SeAT, the incentivised settings apply.
                </p>
            </div>
            <div class="col-sm-12">
                <label>Valuation of Ore:</label>
                <p>
                    Value of ore can be determined with two methods: By ore type OR By
                    mineral content. If you are moon mining, it's better to use mineral content as it's more accurate as
                    Moon Goo is rarely sold by the raw ore, but more often as refined products. This keeps the moon
                    mining honest.
                </p>
            </div>
            <div class="col-sm-12">
                <label>Ore Tax:</label>
                <p>
                    You can specify separate taxes for each category of ore.
                </p>
            </div>
        </div>
    </div>
@endsection
