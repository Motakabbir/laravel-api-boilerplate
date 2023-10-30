<?php
namespace App\Http\Requests\UserRoleAccess;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRoleAccessRequest extends FormRequest
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
 			'role_id' =>  ['required'], 
 			'feature_id' =>  ['required'], 
 			'create' =>  ['nullable'], 
 			'view_others' =>  ['nullable'], 
 			'edit' =>  ['nullable'], 
 			'edit_others' =>  ['nullable'], 
 			'delete' =>  ['nullable'], 
 			'delete_others' =>  ['nullable']
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
