<?php

namespace Denngarr\Seat\Billing;

use RecursiveTree\Seat\TreeLib\Helpers\Setting;

class BillingSettings
{
    public static $GENERATE_TAX_INVOICES;
    public static $TAX_INVOICE_WHITELIST;
    public static $INVOICE_THRESHOLD;

    public static $ALWAYS_SHOW_TAX_CODES;

    public static function init(){
        self::$GENERATE_TAX_INVOICES = Setting::create("billing","tax.generate.invoices", true);
        self::$TAX_INVOICE_WHITELIST = Setting::create("billing","tax.whitelist.corporations", true);
        self::$INVOICE_THRESHOLD = Setting::create("billing","tax.invoices.threshold", true);
        self::$ALWAYS_SHOW_TAX_CODES = Setting::create("billing","tax.codes.always_show", true);
    }
}