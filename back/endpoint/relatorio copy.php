<?php
require_once(dirname(__DIR__) . '/config/autoload.php');
require_once(dirname(__DIR__) . '/config/connection.php');
require_once(dirname(__DIR__) . '/utils/required.php');
require_once(dirname(__DIR__) . '/utils/converterNumeroParaEscrita.php');
require_once(dirname(__DIR__) . '/utils/removeCaracteresEspeciais.php');
require_once(dirname(__DIR__) . '/utils/formatarDataParaTexto.php');
require_once(dirname(__DIR__) . '/utils/formatarHoraExtenso.php');
require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Dompdf\Dompdf;

date_default_timezone_set('America/Sao_Paulo');


$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;


function store($req)
{
	/*Arrumar. Não esta salvando o texto que existe dentro do documento */
	$nome_relatorio = isset($req["nome_relatorio"]) ? $req["nome_relatorio"] : null;
	$periodo = isset($req["periodo"]) ? $req["periodo"] : null;
	required($nome_relatorio, "Informe o nome do relatório");
	required($periodo, "Informe o periodo");
	if (!isset($_FILES["file"]) || $_FILES["file"] === null || $_FILES["file"] === "") {
		echo json_encode(["erro" => true, "msg" => "Envie um documento para salvar como modelo"]);
		die();
	}

	$relatorioObj = new Relatorio();
	$periodoObj = new Periodo();
	$periodo = $periodoObj->buscarPorId($periodo);
	if (!$periodo) {
		echo json_encode(["erro" => true, "msg" => "Este periodo não é válido"]);
		die;
	}
	$nome_path = removeCaracteresEspeciais($periodo["descricao"]);
	$upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "modelos";
	$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

	if (is_dir($upload_dir)) {
		// echo "A Pasta não Existe"; CRIA A SUBPASTA que é o nome do documento enviado
		if (is_dir($upload_dir . DIRECTORY_SEPARATOR . $nome_path)) {
			move_uploaded_file($_FILES['file']['tmp_name'], "$upload_dir" . DIRECTORY_SEPARATOR . "$nome_path" . DIRECTORY_SEPARATOR . "$nome_relatorio.$ext");
			echo json_encode(['erro' => false, 'msg' => 'Arquivo salvo com sucesso']);
		} else {
			mkdir($upload_dir . DIRECTORY_SEPARATOR . $nome_path, 0755);
			move_uploaded_file($_FILES['file']['tmp_name'], "$upload_dir" . DIRECTORY_SEPARATOR . "$nome_path" . DIRECTORY_SEPARATOR . "$nome_relatorio.$ext");
			echo json_encode(['erro' => false, 'msg' => 'Arquivo salvo com sucesso']);
		}
	} else {
		// echo "A Pasta não Existe"; CRIA A PASTA
		mkdir($upload_dir, 0755);

		// echo "A Pasta não Existe"; CRIA A SUBPASTA que é o nome do documento enviado
		if (!is_dir($upload_dir . DIRECTORY_SEPARATOR . $nome_path)) {
			mkdir($upload_dir . DIRECTORY_SEPARATOR . $nome_path, 0755);
			move_uploaded_file($_FILES['file']['tmp_name'], "$upload_dir" . DIRECTORY_SEPARATOR . "$nome_path" . DIRECTORY_SEPARATOR . "$nome_relatorio.$ext");
			echo json_encode(['erro' => false, 'msg' => 'Arquivo salvo com sucesso']);
		}
	}
}

function index($req)
{
	echo json_encode(["erro" => false, "msg" => ""]);
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

function gerarApuracaoATA($req)
{
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
	date_default_timezone_set('America/Sao_Paulo');
	$comissao = (array)json_decode(file_get_contents("../database/comissao.json"));

	// $periodo_id = 8;
	$periodo_id = isset($req["periodo"]) ? $req["periodo"] : null;
	$sala_apuracao = isset($req["sala_apuracao"]) ? $req["sala_apuracao"] : null;
	$instituicao = isset($req["instituicao"]) ? $req["instituicao"] : null;
	$nome_documento = isset($req["documento"]) ? $req["documento"] : "ATA";
	$data = isset($req["data"]) ? date('d-m-Y', strtotime($req["data"])) : date("d-m-Y");
	$horario = isset($req["horario"]) ? $req["horario"] : date("H:i");
	required($periodo_id, "Informe o ID do periodo.");
	required($sala_apuracao, "Informe uma sala para apuração.");
	required($instituicao, "Informe a instituição.");

	$dompdf = new Dompdf();
	$votoObj = new Voto();
	$eleitorObj = new Eleitor();
	$periodoObj = new Periodo();
	$chapaObj = new Chapa();
	$periodo = $periodoObj->buscarPorId($periodo_id);
	if (!$periodo) {
		echo json_encode(["erro" => true, "msg" => "Este periodo não é válido"]);
		die;
	}
	$votos = $votoObj::buscar($periodo_id);

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

	$nome_path = removeCaracteresEspeciais($periodo["descricao"]);
	$upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "modelos" . DIRECTORY_SEPARATOR .'modelo_ata.html';

	$documento = file_get_contents($upload_dir);

	$documento = str_replace('{DATA_TXT}', formatarDataExtensoParaTexto($data), $documento);
	$documento = str_replace('{HORARIO_TXT}', formatarHoraExtensoParaTexto($horario), $documento);
	$documento = str_replace('{SALA_APURACAO}', $sala_apuracao, $documento);
	$documento = str_replace('{LOCAL_APURACAO}', $comissao[$instituicao]->nome_completo, $documento);
	$documento = str_replace('{ENDERECO_LOCAL}', $comissao[$instituicao]->endereco, $documento);
	$documento = str_replace('{NOME_COMISSAO}', $comissao[$instituicao]->nome_comissao, $documento);
	$documento = str_replace('{EDITAL}', $comissao[$instituicao]->edital, $documento);
	$documento = str_replace('{ELEICAO}', $comissao[$instituicao]->eleicao, $documento);

	$MEMBROS = "";
	$ASSINATURAS = "";

	foreach ($comissao[$instituicao]->membros_comissao as $cargo => $membro) {
		if (is_array($membro)) {
			foreach ($membro as $c => $m) {
				$ASSINATURAS_COMISSAO = "_____________________ <br />\n {ASSINATURAS_NOME} <br />\n {ASSINATURAS_CARGO} <br />\n <br />\n  <br />\n ";
				$ASSINATURAS_COMISSAO = str_replace("{ASSINATURAS_NOME}", $m->nome, $ASSINATURAS_COMISSAO);
				$ASSINATURAS_COMISSAO = str_replace("{ASSINATURAS_CARGO}", $cargo, $ASSINATURAS_COMISSAO);

				$MEMBROS_TXT = "{NOME} ({CARGO}), ";
				$MEMBROS_TXT = str_replace("{NOME}", $m->nome, $MEMBROS_TXT);
				$MEMBROS_TXT = str_replace("{CARGO}", $cargo, $MEMBROS_TXT);
				$MEMBROS .= $MEMBROS_TXT;
				$ASSINATURAS .= $ASSINATURAS_COMISSAO;
			}
		} else {
			if ($cargo == "secretario") {
				$documento = str_replace('{SECRETARIO}', $membro->nome, $documento);
			}

			$ASSINATURAS_COMISSAO = "_____________________ <br />\n {ASSINATURAS_NOME} <br />\n {ASSINATURAS_CARGO} <br />\n <br />\n  <br />\n ";
			$ASSINATURAS_COMISSAO = str_replace("{ASSINATURAS_NOME}", $membro->nome, $ASSINATURAS_COMISSAO);
			$ASSINATURAS_COMISSAO = str_replace("{ASSINATURAS_CARGO}", $cargo, $ASSINATURAS_COMISSAO);

			$MEMBROS_TXT = "{NOME} ({CARGO}), ";
			$MEMBROS_TXT = str_replace("{NOME}", $membro->nome, $MEMBROS_TXT);
			$MEMBROS_TXT = str_replace("{CARGO}", $cargo, $MEMBROS_TXT);
			$MEMBROS .= $MEMBROS_TXT;
			$ASSINATURAS .= $ASSINATURAS_COMISSAO;
		}
	}
	$documento = str_replace('{MEMBROS_COMISSAO}', $MEMBROS, $documento);
	$documento = str_replace('{ASSINATURAS}', $ASSINATURAS, $documento);

	$chapas = $chapaObj::buscar($periodo_id);
	$cont_chapa = 0;
	$CHAPA_COMPOSICAO = "";
	$CHAPA_MEMBROS_TXT_AUX = "";

	foreach ($chapas as $chapa) {
		if (isset($chapa["candidatos"]) && count($chapa["candidatos"]) > 0) {
			$CHAPA_NOME_TXT = "{NOME_CHAPA} composta pelo(a) ";
			$CHAPA_NOME_TXT = str_replace("{NOME_CHAPA}", ucfirst($chapa["nome"]), $CHAPA_NOME_TXT);


			foreach ($chapa["candidatos"] as $candidato) {
				$CHAPA_MEMBROS_TXT = "{CANDIDATO_CHAPA}, candidato(a) ao cargo de {CARGO_CHAPA}, ";
				$CHAPA_MEMBROS_TXT = str_replace("{CANDIDATO_CHAPA}", $candidato["nome_candidato"], $CHAPA_MEMBROS_TXT);
				$CHAPA_MEMBROS_TXT = str_replace("{CARGO_CHAPA}", ucfirst($candidato["cargo"]), $CHAPA_MEMBROS_TXT);
				$CHAPA_MEMBROS_TXT_AUX .= $CHAPA_MEMBROS_TXT;
			}

			$CHAPA_NOME_TXT .= $CHAPA_MEMBROS_TXT_AUX;
			$CHAPA_COMPOSICAO .= $CHAPA_NOME_TXT;
			$CHAPA_MEMBROS_TXT_AUX = "";
			$cont_chapa++;
		}
	}

	$documento = str_replace('{QTD_CHAPA_INSCRITO}', $cont_chapa, $documento);
	$documento = str_replace('{QTD_CHAPA_INSCRITO_TXT}', converterNumeroParaEscrita($cont_chapa), $documento);
	$documento = str_replace('{CHAPA_TXT}', $cont_chapa <= 1 ? "chapa" : "chapas", $documento);
	$documento = str_replace('{CHAPA_COMPOSICAO}', $CHAPA_COMPOSICAO, $documento);
	$documento = str_replace('{PROFESSORES_VALIDOS}', $vts["votos_por_categoria"]["professores"], $documento);
	$documento = str_replace('{PROFESSORES_VALIDOS_TXT}', converterNumeroParaEscrita($vts["votos_por_categoria"]["professores"]), $documento);
	$documento = str_replace('{ALUNOS_VALIDOS}', $vts["votos_por_categoria"]["alunos"], $documento);
	$documento = str_replace('{ALUNOS_VALIDOS_TXT}', converterNumeroParaEscrita($vts["votos_por_categoria"]["alunos"]), $documento);
	$documento = str_replace('{FUNCIONARIOS_VALIDOS}', $vts["votos_por_categoria"]["funcionarios"], $documento);
	$documento = str_replace('{FUNCIONARIOS_VALIDOS_TXT}', converterNumeroParaEscrita($vts["votos_por_categoria"]["funcionarios"]), $documento);

	$total = $eleitorObj::total($periodo_id);
	$APURACAO_URNAS = "";
	$APURACAO_FINAL = "";
	$PORCENTAGEM_FINAL = "";
	$APURACAO_FINAL_CHAPAS = "";

	foreach ($total["categorias"] as $t) {
		$APURACAO_TEXTO = "{NOME_CATEGORIA} votantes inscritos – {CATEGORIA_INSCRITOS} ({CATEGORIA_INSCRITOS_TXT}); votaram – {CATEGORIA_INSCRITOS_VOTARAM} ({CATEGORIA_INSCRITOS_VOTARAM_TXT}); não votaram – {CATEGORIA_INSCRITOS_N_VOTARAM} ({CATEGORIA_INSCRITOS_N_VOTARAM_TXT}) ";
		$APURACAO_TEXTO = str_replace("{NOME_CATEGORIA}", ucfirst($t["nome_categoria"]), $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS}", $t["total_inscritos_por_categoria"], $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS_TXT}", converterNumeroParaEscrita($t["total_inscritos_por_categoria"]), $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS_VOTARAM}", $t["total_presentes_por_categoria"], $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS_VOTARAM_TXT}", converterNumeroParaEscrita($t["total_presentes_por_categoria"]), $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS_N_VOTARAM}", $t["total_ausentes_por_categoria"], $APURACAO_TEXTO);
		$APURACAO_TEXTO = str_replace("{CATEGORIA_INSCRITOS_N_VOTARAM_TXT}", converterNumeroParaEscrita($t["total_ausentes_por_categoria"]), $APURACAO_TEXTO);
		$APURACAO_URNAS .= $APURACAO_TEXTO;
	}
	$documento = str_replace('{APURACAO_URNAS}', $APURACAO_URNAS, $documento);

	ksort($vts["categorias"]); //ALUNOS; FUNCIONARIOS; PROFESSORES
	foreach ($vts["categorias"] as $cat) {
		$APURACAO_FINAL_TEXTO = "{NOME_CATEGORIA} que votaram: {VOTOS_VALIDOS_POR_CATEGORIA} ({VOTOS_VALIDOS_POR_CATEGORIA_TXT}); ";
		$APURACAO_FINAL_TEXTO = str_replace("{NOME_CATEGORIA}", ucfirst($cat["nome_categoria"]), $APURACAO_FINAL_TEXTO);
		$APURACAO_FINAL_TEXTO = str_replace("{VOTOS_VALIDOS_POR_CATEGORIA}", $cat["total_votos_validos_por_categoria"], $APURACAO_FINAL_TEXTO);
		$APURACAO_FINAL_TEXTO = str_replace("{VOTOS_VALIDOS_POR_CATEGORIA_TXT}", converterNumeroParaEscrita($cat["total_votos_validos_por_categoria"]), $APURACAO_FINAL_TEXTO);

		foreach ($cat["chapas"] as $c) {
			$numero_formatado = number_format($c["porcentagem_apuracao_eleitoral"], 2);

			$APURACAO_FINAL_CHAPAS_TXT = "votos para a {NOME_CHAPA}: {TOTAL_VOTOS_POR_CATEGORIA} ({TOTAL_VOTOS_POR_CATEGORIA_TXT}), totalizando {PORCENTAGEM}% ({PORCENTAGEM_TXT}); ";
			$APURACAO_FINAL_CHAPAS_TXT = str_replace("{NOME_CHAPA}", ucfirst(strtolower($c["nome_chapa"])), $APURACAO_FINAL_CHAPAS_TXT);
			$APURACAO_FINAL_CHAPAS_TXT = str_replace("{TOTAL_VOTOS_POR_CATEGORIA}", $c["total_votos_na_chapa_por_categoria"], $APURACAO_FINAL_CHAPAS_TXT);
			$APURACAO_FINAL_CHAPAS_TXT = str_replace("{TOTAL_VOTOS_POR_CATEGORIA_TXT}", converterNumeroParaEscrita($c["total_votos_na_chapa_por_categoria"]), $APURACAO_FINAL_CHAPAS_TXT);
			$APURACAO_FINAL_CHAPAS_TXT = str_replace("{PORCENTAGEM}", $numero_formatado, $APURACAO_FINAL_CHAPAS_TXT);
			$APURACAO_FINAL_CHAPAS_TXT = str_replace("{PORCENTAGEM_TXT}", converterNumeroParaEscrita($numero_formatado), $APURACAO_FINAL_CHAPAS_TXT);

			$APURACAO_FINAL_CHAPAS .= $APURACAO_FINAL_CHAPAS_TXT;
		}

		$APURACAO_FINAL_TEXTO .= $APURACAO_FINAL_CHAPAS;
		$APURACAO_FINAL .= $APURACAO_FINAL_TEXTO;
		$APURACAO_FINAL_CHAPAS = "";
	}
	$documento = str_replace('{APURACAO_FINAL}', $APURACAO_FINAL, $documento);

	foreach ($vts["apuracao_por_chapa"] as $k => $a) {
		$numero_formatado = number_format($a["porcentagem_soma_total"], 2);
		$PORCENTAGEM_FINAL_TXT = " {PORCENTAGEM_FINAL}% ({PORCENTAGEM_FINAL_TXT}) para {NOME_CHAPA};";
		$PORCENTAGEM_FINAL_TXT = str_replace("{NOME_CHAPA}", ucfirst(strtolower($a["nome"])), $PORCENTAGEM_FINAL_TXT);
		$PORCENTAGEM_FINAL_TXT = str_replace("{PORCENTAGEM_FINAL}", $numero_formatado, $PORCENTAGEM_FINAL_TXT);
		$PORCENTAGEM_FINAL_TXT = str_replace("{PORCENTAGEM_FINAL_TXT}", converterNumeroParaEscrita($numero_formatado), $PORCENTAGEM_FINAL_TXT);
		$PORCENTAGEM_FINAL .= $PORCENTAGEM_FINAL_TXT;
	}
	$documento = str_replace('{PORCENTAGEM_FINAL}', $PORCENTAGEM_FINAL, $documento);

	$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . "docs" .  DIRECTORY_SEPARATOR . $nome_path;

	if (!is_dir($path)) {
		mkdir($path, 0755);
	}

	
	$dompdf->loadHtml($documento);
	$dompdf->render();
	$dompdf->stream();
	
	$file = $dompdf->output();
	file_put_contents($path.DIRECTORY_SEPARATOR."ATA.pdf", $file, true);

}

call_user_func($_REQUEST["method"],  $request);
