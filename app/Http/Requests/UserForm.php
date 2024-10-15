<?php

namespace App\Http\Requests;

use App\Rules\BirthYearRule;
use App\Rules\GenderRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()     // we are using policies
    {
        return true;
    }

    public function messages()
    {
        return [
            
            "first_name.min" => "يجب أنْ يكون الإسم الأول أكثر من حرفين",
            "first_name.max" => "يجب أنْ يكون الإسم الأول أقل من 255 حرف",
            "first_name.regex" => "يجب أنْ يكون الإسم الأول باللغة العربيّة",
            
            "last_name.min" => "يجب أنْ يكون الإسم الأخير أكثر من حرفين",
            "last_name.max" => "يجب أنْ يكون الإسم الأخير أقل من 255 حرف",
            "last_name.regex" => "يجب أنْ يكون الإسم الأخير باللغة العربيّة",

            "email.unique" => "يجب على البريد الإلكتروني أنْ يكون فريد",
            "email.email" => "صيغة البريد الإلكتروني غير صحيحة",
            "phone_number.unique" => "يجب على رقم الهاتف أنْ يكون فريد",

            "password.min" => "يجب أنْ يكون كلمة السّر أكثر من حرفين",
            "password.max" => "يجب أنْ يكون كلمة السّر أقل من 255 حرف",

            "address.min" => "يجب أنْ يكون العنوان أكثر من حرفين",
            "address.max" => "يجب أنْ يكون العنوان أقل من 255 حرف",

            "profile_picture.max" => "اسم الصورة قد تجاوز ال 500 محرف",
            "profile_picture.mime" => "يجب أنْ تكون صيغة الصّور png , jpeg أو jpg",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ( $this->isMethod('POST') ) {
            return [
                'first_name' => 'required|string|max:255|min:2|regex:/^[\p{Arabic}\s]+$/u',
                'last_name'  => 'required|string|max:255|min:2|regex:/^[\p{Arabic}\s]+$/u',
                'email' => 'required|email|unique:users',
                'password' => "required|string|max:500|min:9",
                'phone_number' => 'required|unique:users|regex:/^0[0-9]{9}/', 
                'address' => "required|string|max:100|min:2",
                'profile_picture' => "image|mimes:jpeg,jpg,png|max:500",

                'gender' => [
                    "required",
                    "integer",
                    new GenderRule()
                ],
    
                'birth_date' => [
                    'required',
                    'string',
                    new BirthYearRule(),
                ],        
            ];
        } else if ( $this->isMethod('PUT') ) {
            return [
                'first_name' => 'string|max:255|min:2|regex:/^[\p{Arabic}\s]+$/u',
                'last_name'  => 'string|max:255|min:2|regex:/^[\p{Arabic}\s]+$/u',
                'email' => 'email',
                'password' => "string|max:500|min:9",
                'phone_number' => 'regex:/^0[0-9]{9}/', 
                'address' => "string|max:100|min:2",
                'profile_picture' => "image|mimes:jpeg,png|max:500",


                'gender' => [
                    "integer",
                    new GenderRule()
                ],

                'birth_date' => [
                    'date',
                    new BirthYearRule(),
                ],

            ];
        }
    }
}
