<?php
declare(strict_types=1);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
$script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($script === '/' ? '' : $script, '/'));
session_name('portal_si'); session_start();
spl_autoload_register(static function(string $class): void { $prefix='Portal\\'; if(str_starts_with($class,$prefix)){ $file=APP_PATH.'/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php'; if(is_file($file)) require $file; } });
function view(string $name,array $data=[]):void{extract($data,EXTR_SKIP);require APP_PATH.'/Views/'.$name.'.php';}
function url(string $route=''):string{return BASE_URL.'/'.ltrim($route,'/');}
function e(?string $value):string{return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function csrf_token():string{return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function csrf_field():string{return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('Solicitação inválida. Atualize a página e tente novamente.');}}
function flash(string $key,?string $message=null):?string{if($message!==null){$_SESSION['flash'][$key]=$message;return null;}$msg=$_SESSION['flash'][$key]??null;unset($_SESSION['flash'][$key]);return $msg;}
$config=require ROOT_PATH.'/config/database.php';
try{$db=new PDO("pgsql:host={$config['host']};port={$config['port']};dbname={$config['name']}",$config['user'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}catch(PDOException){http_response_code(503);exit('Não foi possível conectar ao banco de dados. Verifique a configuração do ambiente.');}
$conteudos=new Portal\Models\Conteudo($db);$eventos=new Portal\Models\Evento($db);$home=new Portal\Controllers\HomeController($conteudos,$eventos);$auth=new Portal\Controllers\AuthController(new Portal\Models\Usuario($db));$conteudoController=new Portal\Controllers\ConteudoController($conteudos,new Portal\Models\Categoria($db),$eventos);$eventoController=new Portal\Controllers\EventoController($eventos);
$path=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'','/');$base=trim(BASE_URL,'/');if($base&&str_starts_with($path,$base))$path=trim(substr($path,strlen($base)),'/');$method=$_SERVER['REQUEST_METHOD'];
if($path===''&&$method==='GET')$home->index();elseif(preg_match('#^conteudos/([^/]+)$#',$path,$m)&&$method==='GET')$home->article($m[1]);elseif($path==='login')$method==='POST'?$auth->login():$auth->loginForm();elseif($path==='cadastro')$method==='POST'?$auth->register():$auth->registerForm();elseif($path==='logout'&&$method==='POST')$auth->logout();elseif($path==='admin')$conteudoController->dashboard();elseif($path==='admin/conteudos'&&$method==='GET')$conteudoController->index();elseif($path==='admin/conteudos/novo')$method==='POST'?$conteudoController->store():$conteudoController->create();elseif($path==='admin/eventos'&&$method==='GET')$eventoController->index();else{http_response_code(404);view('public/not-found');}
