<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class ContactUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            "first_name" => ["required", "min:1", "max:100", "string"],
            "last_name" => ["nullable", "min:1", "max:100", "string"],
            "email" => ["nullable", "min:1", "max:200", "email"],
            "phone" => ["nullable", "min:1", "max:100", "string"],
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response(
            [
                "errors" => $validator->getMessageBag()
            ],
            400
        ));
    }
}
