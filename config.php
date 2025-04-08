<?php
/**
 * Arquivo de configuração do sistema
 * Define constantes de caminhos e configurações básicas
 */

// Verifica se o arquivo está sendo acessado diretamente
if (!defined('PHP_SELF')) {
    define('PHP_SELF', $_SERVER['PHP_SELF']);
}

// ==================================================
// 1. DEFINIÇÃO DE CAMINHOS FÍSICOS
// ==================================================

// Caminho absoluto para a raiz do projeto
$rootPath = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('ROOT_PATH', $rootPath);

// ==================================================
// 2. DEFINIÇÃO DE URL BASE
// ==================================================

// Determina o protocolo (http ou https)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";

// Remove a porta da URL se for a padrão (80 ou 443)
$serverPort = ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : ':' . $_SERVER['SERVER_PORT'];

// Constroi a URL base completa
define('BASE_URL', $protocol . $_SERVER['SERVER_NAME'] . $serverPort . str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH));

// ==================================================
// 3. DEFINIÇÃO DE CAMINHOS PRINCIPAIS
// ==================================================

// Pastas principais
define('ACTIONS_PATH', ROOT_PATH . 'actions' . DIRECTORY_SEPARATOR);
define('DATABASE_PATH', ROOT_PATH . 'database' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('PAGES_PATH', ROOT_PATH . 'pages' . DIRECTORY_SEPARATOR);
define('PROCESS_PATH', ROOT_PATH . 'process' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);
define('TEMPLATES_PATH', ROOT_PATH . 'templates' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// ==================================================
// 4. SUBPASTAS DE ACTIONS
// ==================================================

define('ACTIONS_SECRETARIA_PATH', ACTIONS_PATH . 'secretaria' . DIRECTORY_SEPARATOR);
define('ACTIONS_MATRICULA_PATH', ACTIONS_SECRETARIA_PATH . 'matricula' . DIRECTORY_SEPARATOR);
define('ACTIONS_DIRETOR_GERAL_PATH', ACTIONS_PATH . 'diretor_geral' . DIRECTORY_SEPARATOR);

// ==================================================
// 5. SUBPASTAS DE INCLUDES
// ==================================================

define('INCLUDES_COMMON_PATH', INCLUDES_PATH . 'common' . DIRECTORY_SEPARATOR);
define('INCLUDES_SECRETARIA_PATH', INCLUDES_PATH . 'secretaria' . DIRECTORY_SEPARATOR);
define('INCLUDES_ALUNO_PATH', INCLUDES_PATH . 'aluno' . DIRECTORY_SEPARATOR);
define('INCLUDES_COORDENADOR_PATH', INCLUDES_PATH . 'coordenador' . DIRECTORY_SEPARATOR);
define('INCLUDES_DIRETOR_GERAL_PATH', INCLUDES_PATH . 'diretor_geral' . DIRECTORY_SEPARATOR);
define('INCLUDES_DIRETOR_PEDAGOGICO_PATH', INCLUDES_PATH . 'diretor_pedagogico' . DIRECTORY_SEPARATOR);
define('INCLUDES_PROFESSOR_PATH', INCLUDES_PATH . 'professor' . DIRECTORY_SEPARATOR);

// ==================================================
// 6. SUBPASTAS DE PAGES (DASHBOARDS)
// ==================================================

define('PAGES_ALUNO_PATH', PAGES_PATH . 'aluno' . DIRECTORY_SEPARATOR);
define('PAGES_COORDENADOR_PATH', PAGES_PATH . 'coordenador' . DIRECTORY_SEPARATOR);
define('PAGES_DIRETOR_GERAL_PATH', PAGES_PATH . 'diretor_geral' . DIRECTORY_SEPARATOR);
define('PAGES_DIRETOR_PEDAGOGICO_PATH', PAGES_PATH . 'diretor_pedagogico' . DIRECTORY_SEPARATOR);
define('PAGES_PROFESSOR_PATH', PAGES_PATH . 'professor' . DIRECTORY_SEPARATOR);
define('PAGES_SECRETARIA_PATH', PAGES_PATH . 'secretaria' . DIRECTORY_SEPARATOR);

// ==================================================
// 7. SUBPASTAS DE PROCESS
// ==================================================

define('PROCESS_ALUNO_PATH', PROCESS_PATH . 'aluno' . DIRECTORY_SEPARATOR);
define('PROCESS_CONSULTAS_PATH', PROCESS_PATH . 'consultas' . DIRECTORY_SEPARATOR);
define('PROCESS_SECRETARIA_PATH', PROCESS_PATH . 'secretaria' . DIRECTORY_SEPARATOR);

// ==================================================
// 8. SUBPASTAS DE PUBLIC
// ==================================================

define('PUBLIC_CSS_PATH', PUBLIC_PATH . 'css' . DIRECTORY_SEPARATOR);
define('PUBLIC_IMG_PATH', PUBLIC_PATH . 'img' . DIRECTORY_SEPARATOR);
define('PUBLIC_JS_PATH', PUBLIC_PATH . 'js' . DIRECTORY_SEPARATOR);
define('PUBLIC_LIBRARIES_PATH', PUBLIC_PATH . 'libraries' . DIRECTORY_SEPARATOR);
define('PUBLIC_RECUPERACAO_PATH', PUBLIC_PATH . 'recuperacao' . DIRECTORY_SEPARATOR);

// ==================================================
// 9. SUBPASTAS DE UPLOADS
// ==================================================

define('UPLOADS_ALUNOS_PATH', UPLOADS_PATH . 'alunos' . DIRECTORY_SEPARATOR);
define('UPLOADS_DOCUMENTOS_PATH', UPLOADS_PATH . 'documentos' . DIRECTORY_SEPARATOR);
define('UPLOADS_PROFESSORES_PATH', UPLOADS_PATH . 'professores' . DIRECTORY_SEPARATOR);

// ==================================================
// 10. ARQUIVOS IMPORTANTES
// ==================================================

define('CONEXAO_PATH', DATABASE_PATH . 'conexao.php');
define('AUTH_PATH', INCLUDES_COMMON_PATH . 'auth.php');
define('SESSION_PATH', INCLUDES_COMMON_PATH . 'session.php');
define('PERMISSOES_PATH', INCLUDES_COMMON_PATH . 'permissoes.php');
define('LOGIN_PATH', PUBLIC_PATH . 'login.htm');

// ==================================================
// 11. VERIFICAÇÕES DE SEGURANÇA
// ==================================================

// Verifica se os diretórios essenciais existem
$requiredDirs = [INCLUDES_PATH, PUBLIC_PATH, DATABASE_PATH];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        die("Erro crítico: Diretório não encontrado - " . htmlspecialchars($dir));
    }
}

// Verifica se os arquivos essenciais existem
$requiredFiles = [CONEXAO_PATH, AUTH_PATH];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("Erro crítico: Arquivo não encontrado - " . htmlspecialchars($file));
    }
}

// ==================================================
// 12. CONFIGURAÇÕES ADICIONAIS
// ==================================================

// Configura o fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Habilita erros apenas em desenvolvimento
if (strpos(BASE_URL, 'localhost') !== false || strpos(BASE_URL, '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
}

// Define o charset padrão
header('Content-Type: text/html; charset=utf-8');