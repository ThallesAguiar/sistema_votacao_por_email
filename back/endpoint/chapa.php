<?php
require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;


function store($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	$chapas = isset($req["chapas"]) ? $req["chapas"] : null;
	required($periodo, "Informe um periodo");
	required($chapas, "Informe as chapas");

	if (count($chapas) <= 0) {
		echo json_encode(["erro" => true, "msg" => "Informe pelo menos uma chapa."]);
		die;
	}

	$chapaObj = new Chapa();
	$chapaObj->setPeriodo($periodo);
	foreach ($chapas as $chapa) {
		$chapaObj->setNome($chapa["nome"]);
		$chapaObj::cadastrar($chapaObj);
	}

	echo json_encode(["erro" => false, "msg" => "Chapas registradas"]);
}

function index($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	required($periodo, "Informe um periodo");

	$chapaObj = new Chapa();
	$periodoObj = new Periodo();

	$periodoExist = $periodoObj->buscarPorId($periodo);
	if (!$periodoExist) {
		echo json_encode(["erro" => true, "msg" => "Periodo informado não existe"]);
		die;
	}

	$chapas = $chapaObj::buscar($periodo);
	echo json_encode(["erro" => false, "chapas" => $chapas]);
}

function show($req)
{
	$chapa = isset($req["chapa"]) ? $req["chapa"] : null;
	required($chapa, "Informe uma chapa");

	$chapaObj = new Chapa();
	echo json_encode(["erro" => false, "chapa" => $chapaObj::buscarPorId($chapa)]);
}

function update($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	$nome = isset($req["nome"]) ? $req["nome"] : null;
	$id = isset($req["id"]) ? $req["id"] : null;
	required($periodo, "Informe um periodo");
	required($nome, "Informe o nome da chapa");
	required($id, "Informe o ID da chapa");

	$chapaObj = new Chapa();
	$chapaObj->setId($id);
	$chapaObj->setNome($nome);
	$chapaObj->setPeriodo($periodo);
	$chapaObj::atualizar($chapaObj);

	echo json_encode(["erro" => false, "msg" => "Atualizado com sucesso"]);
}

function delete($req)
{
	$chapa = isset($req["chapa"]) ? $req["chapa"] : null;
	required($chapa, "Informe uma chapa");

	$chapaObj = new Chapa();
	$chapaObj::deletar($chapa);
	echo json_encode(["erro" => false, "msg" => "Excluído com sucesso"]);
}

call_user_func($_REQUEST["method"],  $request);
