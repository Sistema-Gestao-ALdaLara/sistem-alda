<?php

use App\Http\Controllers\Aluno\AlunoController;
use App\Http\Controllers\AutenticateController;
use App\Http\Controllers\Candidato\ProcessoCanditadoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Painel\AlunosController;
use App\Http\Controllers\Painel\AssociacaoController;
use App\Http\Controllers\Painel\CandidatosController;
use App\Http\Controllers\Painel\CursosController;
use App\Http\Controllers\Painel\DisciplinaController;
use App\Http\Controllers\Painel\FuncionariosController;
use App\Http\Controllers\Painel\PainelController;
use App\Http\Controllers\Painel\PautasController;
use App\Http\Controllers\Painel\ProfessorController;
use App\Http\Controllers\Painel\SalasController;
use App\Http\Controllers\Painel\TurmasController;
use App\Http\Controllers\Painel\VagasController;
use App\Models\AnoLectivo;
use App\Models\Funcionario;
use App\Models\Usuario;
use App\Utils\Auxiliar;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '/',
    'middleware' => ['painel']
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('login', [AutenticateController::class, 'index'])->name('login.index');
    Route::post('entrar', [AutenticateController::class, 'entrar'])->name('login.entrar');

    //---------CANDIDATOS | INSCRICAO | SELECAO----------
    Route::post('/inscrever-se', [ProcessoCanditadoController::class, 'inscrever'])->name('candidato.inscrever');
    Route::get('/pegar-dados-candidato/{numero_bi}', [ProcessoCanditadoController::class, 'pegarDadosBi']);
});

Route::group([
    'prefix' => 'painel',
    'middleware' => ['auth']
], function () {

    //==================PAINEL========================
    Route::get('configurações-do-trimestre', [PautasController::class, 'trimestre'])->name('home.trimestre');
    Route::get('fechar-trimestre', [PautasController::class, 'fecharTrimestre'])->name('trimestre.fechar');
    Route::get('voltar-trimestre', [PautasController::class, 'voltarTrimestre'])->name('trimestre.voltar');

    Route::get('/', [PainelController::class, 'index'])->name('home.painel');
    Route::get('/sair', [AutenticateController::class, 'logout'])->name('painel.sair');

    //---------CURSOS----------
    Route::get('/cursos', [CursosController::class, 'index'])->name('home.cursos');
    Route::post('adicionar-curso', [CursosController::class, 'adicionarCurso'])->name('cursos.adicionar');
    Route::post('adicionar-coordenador', [CursosController::class, 'adicionarCoordenador'])->name('coordenador.adicionar');

    //---------FUNCIONÁRIO----------
    Route::get('/funcionarios', [FuncionariosController::class, 'index'])->name('home.funcionarios');

    Route::post('adicionar-funcionario', [FuncionariosController::class, 'adicionarFuncionario'])->name('funcionario.adicionar');
    
    //---------USUÁRIO----------
    Route::post('/adicionar-usuario', [UsuariosController::class, 'adicionarUsuario'])->name('usuario.adicionar');
    Route::put('/editar-usuario/{idUsuario}', [UsuariosController::class, 'editarUsuario'])->name('usuario.editar');
    Route::get('/listar-usuarios', [UsuariosController::class, 'listarTodosUsuarios'])->name('usuario.pegar.todos');
    Route::get('/pegar-usuario/{idusuario}', [UsuariosController::class, 'pegarUsuarioPorId'])->name('usuario.pegar.por_id');
    Route::get('/pegar-usuario/{campo}/{valor}', [UsuariosController::class, 'pegarUsuario'])->name('usuario.pegar');
    Route::delete('/eliminar-usuario/{idUsuario}', [UsuariosController::class, 'eliminarUsuario'])->name('usuario.eliminar');

    //-------------SALAS----------------
    Route::get('/salas', [SalasController::class, 'index'])->name('home.salas');
    Route::post('/salas', [SalasController::class, 'store'])->name('home.store.sala');

    //---------DISCIPLINAS----------
    Route::get('/disciplinas', [DisciplinaController::class, 'index'])->name('home.discplinas');
    Route::post('adicionar-disciplina', [DisciplinaController::class, 'adicionarDisciplina'])->name('disciplinas.adicionar');
    Route::post('editar-disciplina', [DisciplinaController::class, 'adicionarDisciplina'])->name('disciplinas.actualizar');

    //---------ASSOCIAÇÃO----------
    Route::get('/associar-disciplina-e-curso', [AssociacaoController::class, 'disciplinaCurso'])->name('home.disciplina-curso');
    Route::post('adicionar-disciplina-curso', [AssociacaoController::class, 'adicionarDisciplinaCurso'])->name('disciplina-curso.adicionar');
    //-----------------------------------------------------------------------------------
    Route::get('/associar-professor-e-disciplina', [AssociacaoController::class, 'professorDisciplina'])->name('home.professor-disciplina');
    Route::post('adicionar-professor-disciplina', [AssociacaoController::class, 'adicionarProfDisciplina'])->name('professor-disciplina.adicionar');


    //---------VAGAS----------
    Route::get('/vagas', [VagasController::class, 'index'])->name('home.vagas');
    Route::post('adicionar-vaga', [VagasController::class, 'definirVaga'])->name('vagas.adicionar');
    Route::post('actualizar-vaga', [VagasController::class, 'actualizarVaga'])->name('vagas.editar');

    //---------TURMAS----------
    Route::get('/turmas', [TurmasController::class, 'index'])->name('home.turmas');
    Route::post('adicionar-turma', [TurmasController::class, 'adicionarTurma'])->name('turmas.adicionar');

    //---------CANDIDATOS | INSCRICAO | SELECAO | MATRÍCULA----------
    Route::get('/candidatos-pendentes/{status?}', [CandidatosController::class, 'CandidatosPendentes'])->name('candidato.inscritos');
    Route::get('/eliminar-inscrito/{candidato_id}', [CandidatosController::class, 'eliminarInscrito'])->name('candidato.inscrito.eliminar');
    Route::get('/eliminar-selecionado/{candidato_id}', [CandidatosController::class, 'removerSelecionado'])->name('candidato.selecionado.remover');
    Route::post('/selecionar-candidato', [CandidatosController::class, 'selecionarCandidato'])->name('candidato.selecionar');
    Route::get('/candidatos-selecionados', [CandidatosController::class, 'CandidatosSelecionados'])->name('candidato.selecionados');
    Route::get('/candidatos-selecionados', [CandidatosController::class, 'CandidatosSelecionados'])->name('candidato.selecionados');
    Route::get('matricular-candidato/{candidato_id?}', [CandidatosController::class, 'matricularCandidato'])->name('candidato.matricular');
    Route::get('/alunos-matriculados', [CandidatosController::class, 'alunosMatriculados'])->name('aluno.matriculados');
    Route::post('/definir-turma', [CandidatosController::class, 'definirTurmaDoAluno'])->name('aluno.definir-turma');
    
    //---------ALUNOS----------
    //Route::post('/selecionar-candidato', [CandidatosController::class, 'selecionarCandidato'])->name('candidato.selecionar');
    Route::get('/boletim-de-notas/{aluno_id}/{trimestre_id}', [AlunoController::class, 'boletimDeNotas'])->name('boletim');
    Route::get('/relatório-de-notas/{aluno_id}/{trimestre_id}', [AlunoController::class, 'relatorioDeNotas'])->name('relatorio-notas');


    Route::get('/minhas-turmas', [ProfessorController::class, 'index'])->name('professor.index');

    Route::get('/alunos-da-turma/{turm?}', [ProfessorController::class, 'listaAlunos'])->name('professor.alunos');

    Route::get('/classes', [AlunoController::class, 'index'])->name('aluno.index');

    //==================PAUTAS========================
    Route::get('lista-dos-alunos/{turma}/{disciplina?}', [PautasController::class, 'turma'])->name('pauta.listar-alunos');
    Route::get('pauta-trimestral/{turma}', [PautasController::class, 'gerarPautaTrimestral'])->name('pauta.trimestral');
    Route::get('mini-pauta/{turma}/{disciplina_id}', [PautasController::class, 'gerarMiniPautaSingular'])->name('mini-pauta.singular');
    Route::get('mini-pautas/{turma}', [PautasController::class, 'gerarMiniPautas'])->name('mini-pauta.colectiva');
    Route::get('pauta-final/{turma}', [PautasController::class, 'gerarPautaFinal'])->name('pauta-final');
    Route::post('inserir-nota', [PautasController::class, 'adicionarNotas'])->name('pauta.lancar-notas');
});


Route::get('teste', function () {
    /*$grupo1 = "78105";
    $grupo2 = "35";
    $grupo3 = "135";
    for ($i=0; $i <= 25; $i++) { 
        echo "&nbsp &nbsp &nbsp". $grupo1." ".rand(0, 9)." ".$grupo2." ".rand(0, 9)." ".rand(0, 9)." ".$grupo3."<br>";
    }*/
    dd(App\Models\Aluno::pegarUsuarioAluno("carter@gmail.com"));

});
