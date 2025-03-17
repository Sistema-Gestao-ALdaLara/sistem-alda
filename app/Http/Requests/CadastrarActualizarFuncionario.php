<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use function PHPUnit\Framework\isTrue;

class CadastrarActualizarFuncionario extends FormRequest
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
            'numero_bi' => 'required',
            'nome_usuario' => 'required',
            'sexo' => 'required',
            'nacionalidade' => 'required',

            'email' => 'required|email',
            'senha' => 'required|min:6',
            'tipo_usuario' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'numero_bi.riquired' => "Insira o número do B.I",
            'nome_usuario' => "Insira o nome do Funcionário",
            'sexo' => "Escolha um gênero",
            'nacionalidade' => "Insira a nacionalidade do Funcionário",

            'email.required' => "Insira um email",
            'email.email' => "Insira um email válido",
            'senha.required' => "Digite uma senha",
            'senha.min:6' => "A senha deve conter no mínimo 6 caracteres",
            'tipo_usuario.required' => "Selecione um tipo de funcionário"
        ];
    }
}
