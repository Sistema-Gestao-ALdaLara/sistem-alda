<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessoCandidato extends FormRequest
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
            /*'nome_cand' => 'required',
            'n_bi' => 'required|size:14|unique:App\Models\Candidato,n_bi',
            'data_emissao_bi' => 'required',
            'genero' => 'required',
            'contacto' => 'required',
            'contacto_encarregado' => 'required',
            'endereco_electronico' => 'required|email|unique:App\Models\Usuario,email',
            'data_nascimento' => 'required',
            'nome_mae' => 'required',
            'nome_pai' => 'required',
            'morada_actual' => 'required',
            'foto' => 'required|image',

            'media_final' => 'required|integer|size:2',
            'mat_nota' => 'required|integer|size:2',
            'quim_nota' => 'required|integer|size:2',
            'fis_nota' => 'required|integer|size:2',
            'lingua_nota' => 'required|integer|size:2',
            'upload_bi' => 'required|file',
            'upload_cert' => 'required|file',
            'curso1' => 'required',
            'curso2' => 'required',
            'curso3' => 'required',
        ];
    }

    public function messages(){
        return [
            'nome_cand.required' => 'Insira o teu nome',
            'n_bi.required' => 'O número do B.I é obrigatório.',
            'n_bi.unique' => 'Já existe um candidato com esse B.I.',
            'n_bi.size' => 'O B.I deve conter 14 Caracteres.',
            'data_validade_bi' => 'required',
            'genero.required' => 'Escolha o gênero',
            'contacto.required' => 'Insira o número de Telemóvel',
            'contacto_encarregado' => 'Insira o número de Telemóvel do Encarregado',
            'endereco_electronico.required' => 'Insira o email.',
            'endereco_electronico.unique' => 'Já existe um candidato com esse Email.',
            'endereco_electronico.email' => 'Insira um endereço de email válido.',
            'data_nascimento,required' => 'Insira a data de nascimento',
            'nome_mae.required' => 'Insira o nome da mãe',
            'nome_pai.required' => 'Insira o nome do pai',
            'morada_actual' => 'Insira a mora actual',
            'foto.required' => 'A foto é obrigatória',
            'foto.image' => 'Ficheiro inválido. Escolha uma imagem.',

            'media_final.required' => 'Digite a Média Final',
            'media_final.integer' => 'A Média Final deve ser um número inteiro',
            'media_final.size' => 'A Média Final tem de ter 2 dígitos',

            'mat_nota.required' => 'Digite a Média de Matemática',
            'mat_nota.integer' => 'A Média de Matemática deve ser um número inteiro',
            'mat_nota.size' => 'A Média de Matemática tem de ter 2 dígitos',

            'quim_nota.required' => 'Digite a Média de Química',
            'quim_nota.integer' => 'A Média de Química deve ser um número inteiro',
            'quim_nota.size' => 'A Média de Química tem de ter 2 dígitos',

            'fis_nota.required' => 'Digite a Média de Física',
            'fis_nota.integer' => 'A Média de Física deve ser um número inteiro',
            'fis_nota.size' => 'A Média de Física tem de ter 2 dígitos',

            'lingua_nota.required' => 'Digite a Média de L. Portuguesa',
            'lingua_nota.integer' => 'A Média de L. Portuguesa deve ser um número inteiro',
            'lingua_nota.size' => 'A Média de L. Portuguesa tem de ter 2 dígitos',

            'upload_bi.required' => 'Faça o upload do B.I',
            'upload_bi.file' => 'Bilhete inválido. Escolha um ficheiro',
            'upload_cert.required' => 'Faça upload do Certificado',
            'upload_cert.file' => 'Certificado inválido. Escolha um ficheiro',
            'curso1.required' => 'Escolha o 1º curso desejado',
            'curso2.required' => 'Escolha o 2º curso desejado',
            'curso3.required' => 'Escolha o 3º curso desejado',   */
        ];
    }
}
