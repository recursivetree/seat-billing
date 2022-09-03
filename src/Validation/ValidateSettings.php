<?php

namespace Denngarr\Seat\Billing\Validation;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSettings extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'oremodifier'    => 'required|integer|min:0|max:200',
            'oretaxrate'     => 'required|integer|min:0|max:200',
            'bountytaxrate'  => 'required|integer|min:0|max:200',
            'ioremodifier'   => 'required|integer|min:0|max:200',
            'ioretaxrate'    => 'required|integer|min:0|max:200',
            'ibountytaxrate' => 'required|integer|min:0|max:200',
            'irate'          => 'required|integer|min:0|max:200',
        ];
    }
}
