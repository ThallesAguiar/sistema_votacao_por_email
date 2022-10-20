<?php
require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;


function store($req)
{
	setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

	$token = isset($req["token"]) ? $req["token"] : null;
	$chapa = isset($req["chapa"]) ? $req["chapa"] : null;
	required($token, "Você deve informar um token");
	required($chapa, "Você deve informar uma chapa");

	$votoObj = new Voto();
	$eleitorObj = new Eleitor();
	$periodoObj = new Periodo();
	$emailObj = new Email();
	$chapaObj = new Chapa();

	// VALIDA SE O TOKEN É VALIDO
	$validar = $eleitorObj->validar($token, $_ENV["JWT_CODE"]);
	if ($validar["erro"]) {
		echo json_encode($validar);
		die;
	}

	$chapaExist = $chapaObj::buscarPorId($chapa);
	required($chapaExist, "Chapa informada não existe");
	
	$payload = $validar["payload"];
	$periodo = $periodoObj->buscarPorId($payload->periodo);
	// VERIFICA O PRAZO DO TOKEN
	$dentro_do_prazo = $periodoObj->verificaPrazo($periodo["inicio"], $periodo["fim"]);
	if ($dentro_do_prazo === false) {
		echo json_encode(["erro" => true, "msg" => "Autenticação fora do período eleitoral"]);
		die();
	}

	$eleitor = $eleitorObj->buscarPorEmail($payload->email, $payload->periodo);
	if ($eleitor["data_votacao"] != null || $eleitor["votou"] === 1) {
		echo json_encode(["erro" => true, "msg" => "O seu voto já foi computado. Não é possível votar novamente."]);
		die();
	}

	// REGISTRAR VOTO
	$votoObj->setChapa($chapa);
	$votoObj->setCategoria_eleitor($eleitor["categoria"]);
	$votoObj::cadastrar($votoObj);
	$votoObj::registrarVotoNoEleitor($eleitor["email"], $periodo["id"]);

	//DISPARO EMAIL
	$CORPO_MENSAGEM = "<p>
    <b>Parabéns!</b> O seu voto foi computado com sucesso.<br>
    Aguarde o resultado no dia ".strftime('%d de %B de %Y', strtotime($periodo["resultado"])).". <br>
    </p>";

	$emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
	$emailObj->setAssunto("Comprovante de votação");
	$emailObj->setDescricao("Comprovante de votação");

	if ($_ENV["PRODUCAO"] == "false") {
		$emailObj->setDestinatario("thalles.aguiar@unifebe.edu.br");
		$emailObj::enviarEmail($emailObj);
		echo json_encode(["erro" => false, "msg" => "Comprovante enviado para seu e-mail", "resultado"=>"<b>Parabéns!</b> O seu voto foi computado com sucesso.<br>Aguarde o resultado no dia ".strftime('%d de %B de %Y', strtotime($periodo["resultado"]))."."]);
		die;
	}

	$emailObj->setDestinatario($eleitor["email"]);
	$emailObj::enviarEmail($emailObj);

	echo json_encode(["erro" => false, "msg" => "Comprovante enviado para seu e-mail", "resultado"=>"<p><b>Parabéns!</b> O seu voto foi computado com sucesso.</p><p>Aguarde o resultado no dia ".strftime('%d de %B de %Y', strtotime($periodo["resultado"])).".</p>"]);
}

function index($req)
{
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	required($periodo, "Informe o ID do periodo.");

	$votoObj = new Voto();
	$votos = $votoObj::buscar($periodo);

	$vts = [];

	foreach ($votos as $voto) {
		$vts["votos_por_categoria"]["total"] = $voto["total_votos_no_periodo"];
		$vts["votos_por_categoria"][$voto["nome_categoria"]] = $voto["total_votos_validos_por_categoria"];
		$vts["apuracao_por_chapa"][$voto["nome_chapa"]] = [
			"nome" => $voto["nome_chapa"],
			"votos" => $voto["total_votos_na_chapa"],
			"porcentagem_soma_total" => $voto["porcentagem_soma_total"],
		];
	}

	foreach ($votos as $voto) {
		$vts["categorias"][$voto["nome_categoria"]] = [
			"nome_categoria" => $voto["nome_categoria"],
			"categoria_eleitor" => $voto["categoria_eleitor"],
			"total_votos_validos_por_categoria" => $voto["total_votos_validos_por_categoria"],
		];
	}
	foreach ($votos as $voto) {
		$vts["categorias"][$voto["nome_categoria"]]["chapas"][] = [
			"chapa" => $voto["chapa"],
			"nome_chapa" => $voto["nome_chapa"],
			"total_votos_na_chapa" => $voto["total_votos_na_chapa"],
			"total_votos_na_chapa_por_categoria" => $voto["total_votos_na_chapa_por_categoria"],
			"porcentagem_total_votos_por_chapa_e_categoria" => $voto["porcentagem_total_votos_por_chapa_e_categoria"],
			"porcentagem_apuracao_eleitoral" => $voto["porcentagem_apuracao_eleitoral"]
		];
	}

	echo json_encode(["erro" => false, "votos" => $vts]);
}

function show($req)
{
	echo json_encode(["erro" => false, "msg" => ""]);
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
