<?php

namespace R7\Booking\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Urameshibr\Requests\FormRequest;

class EditBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "booking_id" => "required|int|min:0|not_in:0",
            "total_amount" => "nullable",
            "products_mod_array" => "nullable",
            "customer_info" => "nullable",
            "payment_due_type" => "nullable",
            "payment_credit_type" => "nullable",
            "total_dues" => "nullable",
            "product_refund_array" => "nullable",
            "total_credits" => "nullable",
            "refund_message" => "nullable",
            "debit_message" => "nullable",
            "has_product_changed" => "nullable",
            "has_user_changed" => "nullable",
        ];
    }
    /**
     * Custom message for validation
     *
     * @return array
     */

    public function messages(): array
    {
        return [

        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                "success"=>false,
                "error"=>$validator->errors(),
                "message"=>"one or more fields are required"
            ], 422));
    }


    //end of this class
}
