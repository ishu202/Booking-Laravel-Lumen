<?php


namespace R7\Booking\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Urameshibr\Requests\FormRequest;

class CreateBookingRequest extends FormRequest
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
            "total_amount" => "nullable",
            "products_array" => "nullable",
            "customer_info" => "nullable",
            "payment_type" => "nullable"
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
