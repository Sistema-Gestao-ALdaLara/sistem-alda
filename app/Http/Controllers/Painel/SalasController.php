<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Sala;
use App\Models\Salas;
use Illuminate\Http\Request;
use Validator;

class SalasController extends Controller
{
    public function index(){
        $salas = Sala::listarSalas();
        return view('school.salas', compact('salas'));
    }

    public function store(Request $request){
        $rules = [
            'refe' => 'required|unique:sala,refe',
            'capacidade' => 'required'
        ];
        $messages = [
            'refe.required' => 'Preencha o campo Referência',
            'capacidade.required' => 'Preencha o campo Capacidade',
            'refe.unique' => "Não pode existir Referência duplicadas"
        ];
        $validator = Validator::make($request->except('_token'), $rules, $messages);

        if ($validator->fails()) {
            $message = '';
            $messages_l = json_decode(json_encode($validator->messages()), true);
            foreach ($messages_l as $msg) {
                $message .= $msg[0].'<br>';
            }
            return Helper::returnApi($message,500);
         }
         $stmt = Sala::addSala($request->except('_token'));
         if($stmt){
            return Helper::returnApi("Sala adicionada com sucesso",200);
         }
    }
}
