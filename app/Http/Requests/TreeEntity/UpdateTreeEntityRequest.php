<?php
namespace App\Http\Requests\TreeEntity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTreeEntityRequest extends FormRequest
{
    use HttpResponses;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nodeName' =>  ['required'], 
 			'route_name' =>  ['required'], 
 			'route_location' =>  ['required'], 
 			'icon' =>  ['required'], 
 			'status' =>  ['required'] 			
        ];
    }

    /**
     * @param Validator $validator
     * @return HttpResponseException
     */
    public function failedValidation(Validator $validator): HttpResponseException
    {
        throw new HttpResponseException(
            $this->error(
                $validator->errors()->messages(),
                ValidationConstants::ERROR
            )
        );
    }
}

