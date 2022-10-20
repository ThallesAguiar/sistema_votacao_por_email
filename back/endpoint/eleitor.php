<?php

require_once('../config/autoload.php');
require_once('../config/connection.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;

function store($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	if ($periodo === null || $periodo === "") {
		echo json_encode(["erro" => true, "msg" => "Informe um periodo"]);
		die();
	}
	if (!isset($_FILES["file"]) || $_FILES["file"] === null || $_FILES["file"] === "") {
		echo json_encode(["erro" => true, "msg" => "Envie um documento CSV com padrão email;categoria"]);
		die();
	}

	$eleitorObj = new Eleitor();
	$eleitorObj->setPeriodo($periodo);

	$handle = fopen($_FILES["file"]["tmp_name"], "r");
	while ($line = fgetcsv($handle, 1000, ";")) {
		$eleitorObj->setEmail($line[0]);
		$eleitorObj->setCategoria($line[1]);

		$eleitor_existe = $eleitorObj->buscarPorEmail($line[0], $eleitorObj->getPeriodo());
		if(!$eleitor_existe){
			$eleitorObj::cadastrar($eleitorObj);
		}

	}

	fclose($handle);

	echo json_encode(["erro" => false, "msg" => "Eleitores cadastrados"]);
}

function index($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	if ($periodo === null || $periodo === "") {
		echo json_encode(["erro" => true, "msg" => "Informe um periodo"]);
		die();
	}

	$eleitorObj = new Eleitor();
	$periodoObj = new Periodo();

	$eleitorObj->setPeriodo($periodo);
	$eleitorObj->setCategoria(isset($req['categoria']) ? $req['categoria'] : null);
	$eleitorObj->setVotou(isset($req['votou']) ? $req['votou'] : null);

	$periodo = $periodoObj->buscarPorId($eleitorObj->getPeriodo());
	$eleitores = $eleitorObj->buscarPorPeriodo($eleitorObj->getPeriodo(), $eleitorObj->getCategoria(), $eleitorObj->getVotou());
	$total = $eleitorObj::total($eleitorObj->getPeriodo());

	echo json_encode(["erro" => false, "periodo" => $periodo, "eleitores" => $eleitores, "total"=>$total]);
}

function show($req)
{
	setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');
	
	$token = isset($req["token"]) ? $req["token"] : null;
	if ($token === null || $token === "") {
		echo json_encode(["erro" => true, "msg" => "Informe um token."]);
		die();
	}

	$eleitorObj = new Eleitor();
	$periodoObj = new Periodo();

	$validar = $eleitorObj->validar($token, $_ENV["JWT_CODE"]);

	if ($validar["erro"]) {
		echo json_encode($validar);
		die;
	}

	$payload = $validar["payload"];
	$retorno["periodo"] = $periodoObj->buscarPorId($payload->periodo);

	$dentro_do_prazo = $periodoObj->verificaPrazo($retorno["periodo"]["inicio"], $retorno["periodo"]["fim"]);

	if ($dentro_do_prazo === false) {
		echo json_encode(["erro" => true, "msg" => "Período para votar: dia ".strftime('%d de %B de %Y (%A)', strtotime($retorno["periodo"]["inicio"])) . ", das ".date('H:i', strtotime($retorno["periodo"]["inicio"]))." às ".date('H:i', strtotime($retorno["periodo"]["fim"]))."."]);
		die();
	}

	$retorno["eleitor"] = $eleitorObj->buscarPorEmail($payload->email, $payload->periodo);

	echo json_encode(["erro" => false, "msg" => "Token válido", "retorno" => $retorno]);
}



call_user_func($_REQUEST["method"],  $request);
