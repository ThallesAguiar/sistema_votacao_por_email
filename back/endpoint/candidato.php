<?php
require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;


function store($req)
{
	$candidatos = isset($req["candidatos"]) ? $req["candidatos"] : null;
	required($candidatos, "Informe os candidatos");
	
	if (count($candidatos) <= 0) {
		echo json_encode(["erro" => true, "msg" => "Não pode enviar candidatos vazios"]);
		die;
	}

	$candidatoObj = new Candidato();
	foreach ($candidatos as $candidato) {
		$candidatoObj->setChapa($candidato["chapa"]);
		$candidatoObj->setNome($candidato["nome"]);
		$candidatoObj->setCargo($candidato["cargo"]);
		$candidatoObj::cadastrar($candidatoObj);
	}

	echo json_encode(["erro" => false, "msg" => "Candidatos registradas"]);
}

function index($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	$candidatoObj = new Candidato();
	$candidatos = $candidatoObj::buscar($periodo);
	echo json_encode(["erro" => false, "candidatos" => $candidatos]);
}

function show($req)
{
	$candidato = isset($req["candidato"]) ? $req["candidato"] : null;
	required($candidato, "Informe o ID do candidato");

	$candidatoObj = new Candidato();
	echo json_encode(["erro" => false, "candidato" => $candidatoObj::buscarPorId($candidato)]);
}

function update($req)
{
	$id = isset($req["id"]) ? $req["id"] : null;
	$chapa = isset($req["chapa"]) ? $req["chapa"] : null;
	$nome = isset($req["nome"]) ? $req["nome"] : null;
	$cargo = isset($req["cargo"]) ? $req["cargo"] : null;
	required($id, "Informe o ID do candidato");
	required($chapa, "Informe o ID da chapa");
	required($nome, "Informe o nome do candidato");
	required($cargo, "Informe o cargo do candidato");

	$candidatoObj = new Candidato();
	$candidatoObj->setId($id);
	$candidatoObj->setChapa($chapa);
	$candidatoObj->setNome($nome);
	$candidatoObj->setCargo($cargo);
	$candidatoObj::atualizar($candidatoObj);
	echo json_encode(["erro" => false, "msg" => "Candidato atualizado"]);
}

function delete($req)
{
	$candidato = isset($req["candidato"]) ? $req["candidato"] : null;
	required($candidato, "Informe o ID do candidato");

	$candidatoObj = new Candidato();
	$candidatoObj::deletar($candidato);
	echo json_encode(["erro" => false, "msg" => "Excluído com sucesso"]);
}

call_user_func($_REQUEST["method"],  $request);
