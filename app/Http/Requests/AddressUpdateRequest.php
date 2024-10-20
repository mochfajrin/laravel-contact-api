<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            "postal_code" => ["nullable", "min:1", "max:10", "string"],
            "street" => ["nullable", "min:1", "max:100", "string"],
            "city" => ["nullable", "min:1", "max:100", "string"],
            "province" => ["nullable", "min:1", "max:100", "string"],
            "country" => ["required", "min:1", "max:100", "string"],
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response([
                "errors" => $validator->getMessageBag()
            ], 400)
        );
    }
}
