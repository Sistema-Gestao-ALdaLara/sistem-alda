<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InserirActualizarNota extends FormRequest
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
            'mac' => 'required',
            'npp' => 'required',
            'npt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'mac.required' => 'Insira a MAC do aluno',
            'npp.required' => 'Insira a NPP do aluno',
            'npt.required' => 'Insira a NPT do aluno',
        ];
    }
}
