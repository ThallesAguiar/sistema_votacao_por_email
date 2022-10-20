<?php

require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;

function store($req)
{
	$inicio = isset($req["inicio"]) ? $req["inicio"] : null;
	$fim = isset($req["fim"]) ? $req["fim"] : null;
	$descricao = isset($req["descricao"]) ? $req["descricao"] : null;
	$msg = "Informe uma ";

	required($inicio, $msg . "data de inicio");
	required($fim, $msg . "data de fim");
	required($descricao, $msg . "descrição para este periodo");

	$periodoObj = new Periodo();

	$hr_inicio = isset($req["hr_inicio"]) && $req["hr_inicio"] != "" ? $req["hr_inicio"] : "00:00:00";
	$hr_fim = isset($req["hr_fim"]) && $req["hr_fim"] != "" ? $req["hr_fim"] : "23:59:59";
	$resultado = isset($req["resultado"]) && $req["resultado"] != "" ? date("Y-m-d", strtotime($req["resultado"])) : date("Y-m-d", strtotime($req["fim"]));


	$periodoObj->setInicio(date("Y-m-d", strtotime($inicio)) . ' ' . $hr_inicio);
	$periodoObj->setFim(date("Y-m-d", strtotime($fim)) . ' ' . $hr_fim);
	$periodoObj->setDescricao($descricao);
	$periodoObj->setResultado($resultado);

	$periodoObj::criarPeriodo($periodoObj);

	echo json_encode(["erro" => false, "msg" => "Periodo criado com sucesso"]);
}

function index($req)
{
	$periodoObj = new Periodo();

	echo json_encode(["erro" => false, "periodos" => $periodoObj::buscarPeriodos()]);
}

function show($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	required($periodo, "Informe o ID do periodo.");

	$periodoObj = new Periodo();
	$periodoObj->setId($periodo);
	$periodo = $periodoObj->buscarPorId($periodoObj->getId());

	$inicio["data"] = date("d-m-Y", strtotime($periodo["inicio"]));
	$inicio["hora"] = date("H:i:s", strtotime($periodo["inicio"]));
	$periodo["inicio"] = $inicio;

	$fim["data"] = date("d-m-Y", strtotime($periodo["fim"]));
	$fim["hora"] = date("H:i:s", strtotime($periodo["fim"]));
	$periodo["fim"] = $fim;

	$resultado["data"] = date("d-m-Y", strtotime($periodo["resultado"]));
	$resultado["hora"] = date("H:i:s", strtotime($periodo["resultado"]));
	$periodo["resultado"] = $resultado;

	echo json_encode(["erro" => false, "periodo" => $periodo]);
}

function update($req)
{
	$inicio = isset($req["inicio"]) ? $req["inicio"] : null;
	$fim = isset($req["fim"]) ? $req["fim"] : null;
	$descricao = isset($req["descricao"]) ? $req["descricao"] : null;
	$id = isset($req["id"]) ? $req["id"] : null;
	$msg = "Informe uma ";
	required($id, "Informe o ID do periodo.");
	required($inicio, $msg . "data de inicio");
	required($fim, $msg . "data de fim");
	required($descricao, $msg . "descrição para este periodo");

	$periodoObj = new Periodo();

	$inicio = date("Y-m-d", strtotime($req["inicio"])) . ' ' . $req["hr_inicio"];
	$fim = date("Y-m-d", strtotime($req["fim"])) . ' ' . $req["hr_fim"];
	$resultado = isset($req["resultado"]) && $req["resultado"] != "" ? date("Y-m-d", strtotime($req["resultado"])) : date("Y-m-d", strtotime($req["fim"]));

	$periodoObj->setId($id);
	$periodoObj->setInicio($inicio);
	$periodoObj->setFim($fim);
	$periodoObj->setDescricao($descricao);
	$periodoObj->setResultado($resultado);
	$periodoObj::atualizarPeriodo($periodoObj);

	echo json_encode(["erro" => false, "msg" => "Atualizado com sucesso"]);
}

function delete($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	required($periodo, "Informe o ID do periodo.");

	$periodoObj = new Periodo();
	$periodoObj::deletarPeriodo($periodo);
	echo json_encode(["erro" => false, "msg" => "Excluído com sucesso"]);
}



call_user_func($_REQUEST["method"],  $request);
