<?php
require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;

/**ARQUICO DEFAULT PARA INICIAR UM ENDPOINT */

function store($req)
{
	echo json_encode(["erro" => false, "msg" => ""]);
}

function index($req)
{
	
	$comissao = (array)json_decode(file_get_contents("../database/comissao.json"));
	echo json_encode(["erro" => false, "comissao" => $comissao]);
}

function show($req)
{
	$comissao = (array)json_decode(file_get_contents("../database/comissao.json"));
	if(isset($req["empresa"]) && $req["empresa"] != "" ){
		$comissao = $comissao[$req["empresa"]];
	}
	echo json_encode($comissao);
}

function update($req)
{
	echo json_encode(["erro" => false, "msg" => ""]);
}

function delete($req)
{
	echo json_encode(["erro" => false, "msg" => ""]);
}

call_user_func($_REQUEST["method"],  $request);
