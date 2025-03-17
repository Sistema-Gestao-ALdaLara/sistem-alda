<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CadastrarActualizarFuncionario;
use App\Models\Funcionario;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use App\Utils\Auxiliar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionariosController extends Controller
{
    public function index(){
        $tipos_de_usuarios = TipoUsuario::listarTipos();
        $funcionarios = Funcionario::listarFuncionarios();

        return view('school.funcionarios', compact('tipos_de_usuarios', 'funcionarios'));
    }

    public function adicionarFuncionario(Request $request){
        try {
            DB::beginTransaction();

            $foto = Auxiliar::uploadImagem($request->file('foto'), 'storage/funcionario/');
            $idUsuario = Usuario::cadastrarUsuario($request->nome_usuario, $request->email, $request->senha, $request->tipo_usuario);

            Funcionario::cadastrarFuncionario($request->numero_bi,
                                                        $request->nome_usuario,
                                                        $request->telefone,
                                                        $foto, $request->sexo,
                                                        $idUsuario,
                                                        $request->data_nascimento,
                                                        $request->endereco,
                                                        $request->nacionalidade,
                                                        $request->provincia);

            DB::commit();

            return redirect()->back()->with('success', "Funcionário adiconado com sucesso.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput($request->all())->withErrors("Aconteceu um erro ao adicionar o funcionário. Se e o persistir contacte um assistente. ");
        }
    }

    public function actualizarFuncionario(Request $request, $usuario_id){
        try {
            DB::beginTransaction();

            if($request->foto != null)
                $foto = Auxiliar::uploadImagem($request->file('foto'), 'storage/funcionario/');
                
            $idUsuario = $usuario_id;

            Funcionario::cadastrarFuncionario($request->numero_bi,
                                                        $request->nome_usuario,
                                                        $request->telefone,
                                                        $foto, $request->sexo,
                                                        $idUsuario,
                                                        $request->data_nascimento,
                                                        $request->endereco,
                                                        $request->nacionalidade,
                                                        $request->provincia);

            DB::commit();

            return redirect()->back()->with('success', "Funcionário adiconado com sucesso.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput($request->all())->withErrors("Aconteceu um erro ao adicionar o funcionário. Se e o persistir contacte um assistente. ");
        }
    }
}
