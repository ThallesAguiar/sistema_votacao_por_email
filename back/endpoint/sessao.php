<?php

require_once('../config/autoload.php');
require_once('../config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');
require_once(dirname(__DIR__) . '/utils/converterNumeroParaEscrita.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;

function store($req)
{

    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    $header = json_encode($header);
    $header = base64_encode($header);

    $payload = [
        'email' => $req["email"],
        'periodo' => $req["periodo"]
    ];
    $payload = json_encode($payload);
    $payload = base64_encode($payload);

    $signature = hash_hmac('sha256', "$header.$payload", $_ENV["JWT_CODE"], true);
    $signature = base64_encode($signature);

    $token = "$header.$payload.$signature";


    echo json_encode($token);
}

function validar($req)
{
    $part = explode(".", $req["token"]);
    $header = $part[0];
    $payload = $part[1];
    $signature = $part[2];

    $valid = hash_hmac('sha256', "$header.$payload", $_ENV["JWT_CODE"], true);
    $valid = base64_encode($valid);


    if ($signature != $valid) {
        echo json_encode(["erro" => true, "msg" => "Autenticação inválida", "payload" => null]);
        die();
    }

    $payload = base64_decode($payload);
    $payload = json_decode($payload);

    echo json_encode(["erro" => false, "msg" => "validado", "payload" => $payload]);
}

function index($req)
{
    $eleitorObj = new Eleitor();
    $periodoObj = new Periodo();

    $eleitorObj->setPeriodo($req['periodo']);
    $eleitorObj->setCategoria(isset($req['categoria']) ? $req['categoria'] : null);
    $eleitorObj->setVotou(isset($req['votou']) ? $req['votou'] : null);

    $periodo = $periodoObj->buscarPorId($eleitorObj->getPeriodo());
    $eleitores = $eleitorObj->buscarPorPeriodo($eleitorObj->getPeriodo(), $eleitorObj->getCategoria(), $eleitorObj->getVotou());

    $eleitoresAuth = [];
    foreach ($eleitores as $eleitor) {
        $token = $eleitorObj->gerarToken($eleitor["email"], $eleitor["periodo"], $_ENV["JWT_CODE"]);

        $eleitor["token"] = $token;
        $eleitoresAuth[] = $eleitor;
    }

    echo json_encode(["erro" => false, "periodo" => $periodo, "eleitores" => $eleitoresAuth]);
}

function logar($req)
{
    $login = isset($req["login"]) ? $req["login"] : null;
    $senha = isset($req["senha"]) ? $req["senha"] : null;
    $instituicao = isset($req["instituicao"]) ? $req["instituicao"] : null;
	required($login, "Informe um login");
	required($senha, "Informe uma senha");
	required($instituicao, "Informe a sua instituição");

    $comissaoObj = new Comissao;
    $eleitorObj = new Eleitor;

    $usuario = $comissaoObj::autenticar($login, $senha, $instituicao);
    $token = $eleitorObj->gerarToken($usuario->email, $usuario->periodo, $_ENV["JWT_CODE"]);
    
    echo json_encode(["erro" => false, "msg" => "Usuário autenticado", "usuario" => $usuario,"token"=>$token]);
}


call_user_func($_REQUEST["method"],  $request);
