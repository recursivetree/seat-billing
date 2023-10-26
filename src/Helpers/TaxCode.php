<?php

namespace Denngarr\Seat\Billing\Helpers;

use Denngarr\Seat\Billing\Models\TaxInvoice;
use Exception;

class TaxCode
{
    private const CURRENT_VERSION = 0x01;
    public const SINGLE_INVOICE_CODE = 0xEC;
    public const CORPORATION_INVOICE_CODE = 0xCC;

    private static function generateTaxCode($type, $id)
    {
        return sprintf("tax%02X%02X%016X", self::CURRENT_VERSION, $type, $id);
    }

    public static function generateInvoiceCode($invoice_id)
    {
        return self::generateTaxCode(self::SINGLE_INVOICE_CODE, $invoice_id);
    }

    public static function generateCorporationCode($corporation_id)
    {
        return self::generateTaxCode(self::CORPORATION_INVOICE_CODE, $corporation_id);
    }

    public static function decodeTaxCode($code)
    {
        $code = trim($code);

        if(substr($code, 0, 3) !== "tax") return null;

        $version_str = substr($code, 3, 2);
        // php quirks: false if an invalid operation
        if ($version_str === false) return null;
        $version = hexdec($version_str);

        switch ($version) {
            case 0x01:{
                return self::decodeV1(substr($code, 5));
            }
            case 0:
            default: {
                return null;
            }
        }
    }

    private static function decodeV1($code){
        $type_str = substr($code, 0, 2);
        // php quirks: false if an invalid operation
        if ($type_str === false) return null;
        $code_type = hexdec($type_str);

        $type = null;
        switch ($code_type){
            case self::CORPORATION_INVOICE_CODE:
            case self::SINGLE_INVOICE_CODE: {
                $type = $code_type;
                break;
            }
            default: {
                return null;
            }
        }

        $id_str = substr($code, 2, 16);
        // php quirks: false if an invalid operation
        if ($id_str === false) return null;
        $id = hexdec($id_str);

        return new TaxCode($type, $id);
    }

    /**
     * @param $type
     * @param $id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    public $type;
    public $id;

    /**
     * @throws Exception
     */
    public function getTaxInvoices($user_id){
        switch ($this->type){
            case self::SINGLE_INVOICE_CODE: {
                return TaxInvoice::where("id",$this->id)
                    ->get();
            }
            case self::CORPORATION_INVOICE_CODE: {
                return TaxInvoice::where("receiver_corporation_id",$this->id)
                    ->where("user_id", $user_id)
                    ->get();
            }
            default:{
                throw new Exception("Invalid code type");
            }
        }
    }
}