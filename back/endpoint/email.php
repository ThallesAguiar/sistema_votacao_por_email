<?php

require_once('../config/autoload.php');
require_once('../config/connection.php');

$json = json_decode(file_get_contents("php://input"), true);
$request = isset($json) ? $json : $_REQUEST;

/**metodo que notifica os eleitores, envia o link de votação */
function notificar($req)
{
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    if (!isset($req["periodo"]) || $req["periodo"] === null) {
        echo json_encode(["erro" => true, "msg" => "Você deve informar um periodo"]);
        die;
    }

    $eleitorObj = new Eleitor();
    $periodoObj = new Periodo();
    $emailObj = new Email();

    $eleitorObj->setPeriodo($req['periodo']);
    $eleitorObj->setCategoria(isset($req['categoria']) ? $req['categoria'] : null);
    $eleitorObj->setVotou(isset($req['votou']) ? $req['votou'] : null);

    $periodo = $periodoObj->buscarPorId($eleitorObj->getPeriodo());
    $dentro_do_prazo = $periodoObj->verificaPrazo($periodo["inicio"], $periodo["fim"]);
    $hoje = date('Y-m-d H:i:s');

    if (strtotime($hoje) > strtotime($periodo["fim"])) {
        echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta com prazo vencido"]);
        die();
    }
    // if ($dentro_do_prazo === false) {
    //     echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta fora do prazo de votar"]);
    //     die();
    // }

    $eleitores = $eleitorObj->buscarPorPeriodo($eleitorObj->getPeriodo(), $eleitorObj->getCategoria(), $eleitorObj->getVotou());

    $emailObj->setAssunto($periodo["descricao"]);
    $emailObj->setDescricao(isset($req["descricao"]) ? $req["descricao"] : $periodo["descricao"]);

    $emailsEnviados = [];
    $i = 0;

    foreach ($eleitores as $eleitor) {

        $emailEnviado = $emailObj::jaRecebeuEmail($periodo["descricao"], $eleitor["email"]);

        if (!$emailEnviado) {
            $CORPO_MENSAGEM = "<p>Olá,<br>
                Para votar no {DESCRICAO_PERIODO} - <b><a href='{LINK}'> acesse aqui</a></b>.<br>
                A votação ocorrerá no dia " . strftime('%d de %B de %Y (%A)', strtotime($periodo["inicio"])) . " 
                no horário das " . date('H:i', strtotime($periodo["inicio"])) . " às " . date('H:i', strtotime($periodo["fim"])) . ". <br>
                O presente e-mail é de <b>USO PESSOAL</b> e <b>INTRANSFERÍVEL</b>. <br>
                </p>";

            $CORPO_MENSAGEM = str_replace("{DESCRICAO_PERIODO}", $periodo["descricao"], $CORPO_MENSAGEM);

            $token = $eleitorObj->gerarToken($eleitor["email"], $eleitor["periodo"], $_ENV["JWT_CODE"]);
            $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
            $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
            $emailObj->setDestinatario($eleitor["email"]);
            $emailObj::enviarEmail($emailObj);

            $emailsEnviados[] = [
                "email" => $eleitor['email'],
                "categoria" => $eleitor['categoria']
            ];
            
            $i++;
        }
    }

    echo json_encode(["erro" => false, "msg" => "E-mails enviados", "emailsEnviados" => $emailsEnviados, "totalEnviados"=>$i]);
    die;

    // foreach ($eleitores as $eleitor) {
    //     $CORPO_MENSAGEM = "<p>Olá,<br>
    //     Para votar na {DESCRICAO_PERIODO} - <b><a href='{LINK}'> acesse aqui</a></b>.<br>
    //     A votação ocorrerá no dia " . strftime('%d de %B de %Y (%A)', strtotime($periodo["inicio"])) . " 
    //     no horário das " . date('H:i', strtotime($periodo["inicio"])) . " às " . date('H:i', strtotime($periodo["fim"])) . ". <br>
    //     O presente e-mail é de <b>USO PESSOAL</b> e <b>INTRANSFERÍVEL</b>. <br>
    //     </p>";

    //     $CORPO_MENSAGEM = str_replace("{DESCRICAO_PERIODO}", $periodo["descricao"], $CORPO_MENSAGEM);

    //     $token = $eleitorObj->gerarToken($eleitor["email"], $eleitor["periodo"], $_ENV["JWT_CODE"]);
    //     $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
    //     $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
    //     $emailObj->setDestinatario($eleitor["email"]);
    //     $emailObj::enviarEmail($emailObj);
    // }
    // echo json_encode(["erro" => false, "msg" => "E-mails enviados", "eleitores" => $eleitores]);
}

/**convocar os eleitores que ainda não votaram */
function convocarOsPendentes($req)
{
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    if (!isset($req["periodo"]) || $req["periodo"] === null) {
        echo json_encode(["erro" => true, "msg" => "Você deve informar um periodo"]);
        die;
    }

    $eleitorObj = new Eleitor();
    $periodoObj = new Periodo();
    $emailObj = new Email();

    $eleitorObj->setPeriodo($req['periodo']);
    $eleitorObj->setCategoria(isset($req['categoria']) ? $req['categoria'] : null);
    $eleitorObj->setVotou(0); //Zero pois vai chamar quem ainda não votou

    $periodo = $periodoObj->buscarPorId($eleitorObj->getPeriodo());
    $dentro_do_prazo = $periodoObj->verificaPrazo($periodo["inicio"], $periodo["fim"]);
    $hoje = date('Y-m-d H:i:s');

    if (strtotime($hoje) > strtotime($periodo["fim"])) {
        echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta com prazo vencido"]);
        die();
    }
    // if ($dentro_do_prazo === false) {
    //     echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta fora do prazo de votar"]);
    //     die();
    // }

    $eleitores = $eleitorObj->buscarPorPeriodo($eleitorObj->getPeriodo(), $eleitorObj->getCategoria(), $eleitorObj->getVotou());

    $emailObj->setAssunto($periodo["descricao"]);
    $emailObj->setDescricao(isset($req["descricao"]) ? $req["descricao"] : "Eleição " . date("Y"));

    $CORPO_MENSAGEM = "<p>Olá,<br>
    Para votar no {DESCRICAO_PERIODO} - <b><a href='{LINK}'> acesse aqui</a></b>.<br>
    A votação ocorrerá no dia " . strftime('%d de %B de %Y (%A)', strtotime($periodo["inicio"])) . " 
    no horário das " . date('H:i', strtotime($periodo["inicio"])) . " às " . date('H:i', strtotime($periodo["fim"])) . ". <br>
    O presente e-mail é de <b>USO PESSOAL</b> e <b>INTRANSFERÍVEL</b>. <br>
    </p>";

    $CORPO_MENSAGEM = str_replace("{DESCRICAO_PERIODO}", $periodo["descricao"], $CORPO_MENSAGEM);

    if ($_ENV["PRODUCAO"] == "false") {
        $token = $eleitorObj->gerarToken("thalles.aguiar@unifebe.edu.br", $eleitores[0]["periodo"], $_ENV["JWT_CODE"]);
        $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
        $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
        $emailObj->setDestinatario("thalles.aguiar@unifebe.edu.br");
        $emailObj::enviarEmail($emailObj);

        echo json_encode(["erro" => false, "msg" => "E-mails enviados", "eleitores" => $_ENV["PRODUCAO"]]);
        die;
    }

    foreach ($eleitores as $eleitor) {
        $token = $eleitorObj->gerarToken($eleitor["email"], $eleitor["periodo"], $_ENV["JWT_CODE"]);
        $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
        $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
        $emailObj->setDestinatario($eleitor["email"]);
        $emailObj::enviarEmail($emailObj);
    }
    echo json_encode(["erro" => false, "msg" => "E-mails enviados", "eleitores" => $eleitores]);
}

/**envia o comprovante de votação */
function comprovanteDeVoto($req)
{
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    if (!isset($req["token"]) || $req["token"] === null) {
        echo json_encode(["erro" => true, "msg" => "Você deve informar um token"]);
        die;
    }

    $eleitorObj = new Eleitor();
    $periodoObj = new Periodo();
    $emailObj = new Email();

    $validar = $eleitorObj->validar($req["token"], $_ENV["JWT_CODE"]);

    if ($validar["erro"]) {
        echo json_encode($validar);
        die;
    }

    $payload = $validar["payload"];

    $periodo = $periodoObj->buscarPorId($payload->periodo);

    $dentro_do_prazo = $periodoObj->verificaPrazo($periodo["inicio"], $periodo["fim"]);

    if ($dentro_do_prazo === false) {
        echo json_encode(["erro" => true, "msg" => "Token expirado"]);
        die();
    }

    $eleitor = $eleitorObj->buscarPorEmail($payload->email, $payload->periodo);

    $CORPO_MENSAGEM = "<p>
    <b>Parabéns!</b> O seu voto foi computado com sucesso.<br>
    Aguarde o resultado no dia " . strftime('%d de %B de %Y', strtotime($periodo["resultado"])) . ". <br>
    </p>";

    $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
    $emailObj->setAssunto("Comprovante de votação");
    $emailObj->setDescricao("Comprovante de votação");

    if ($_ENV["PRODUCAO"] == "false") {
        $emailObj->setDestinatario("thalles.aguiar@unifebe.edu.br");
        $emailObj::enviarEmail($emailObj);
        echo json_encode(["erro" => false, "msg" => "Comprovante enviado para seu e-mail"]);
        die;
    }

    $emailObj->setDestinatario($eleitor["email"]);
    $emailObj::enviarEmail($emailObj);

    echo json_encode(["erro" => false, "msg" => "Comprovante enviado para seu e-mail"]);
}

/**envia e-mail para um unico email */
function notificarIndividualmente($req)
{
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    if (!isset($req["email"]) || $req["email"] === null) {
        echo json_encode(["erro" => true, "msg" => "Você deve informar um email"]);
        die;
    }
    if (!isset($req["periodo"]) || $req["periodo"] === null) {
        echo json_encode(["erro" => true, "msg" => "Você deve informar um periodo"]);
        die;
    }

    $eleitorObj = new Eleitor();
    $periodoObj = new Periodo();
    $emailObj = new Email();

    $eleitorObj->setPeriodo($req['periodo']);
    $eleitorObj->setEmail($req['email']);

    $periodo = $periodoObj->buscarPorId($eleitorObj->getPeriodo());
    $dentro_do_prazo = $periodoObj->verificaPrazo($periodo["inicio"], $periodo["fim"]);
    $hoje = date('Y-m-d H:i:s');

    if (strtotime($hoje) > strtotime($periodo["fim"])) {
        echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta com prazo vencido"]);
        die();
    }
    // if ($dentro_do_prazo === false) {
    //     echo json_encode(["erro" => true, "msg" => "Você não pode convocar eleitores deste periodo, este periodo esta fora do prazo de votar"]);
    //     die();
    // }

    $eleitor = $eleitorObj->buscarPorEmail($eleitorObj->getEmail(), $eleitorObj->getPeriodo());
    if (!$eleitor) {
        echo json_encode(["erro" => true, "msg" => "Este eleitor não esta na base de dados, ou este eleitor não esta vinculado a este periodo"]);
        die();
    }

    $emailObj->setAssunto($periodo["descricao"]);
    $emailObj->setDescricao(isset($req["descricao"]) ? $req["descricao"] : "Eleição " . date("Y"));

    $CORPO_MENSAGEM = "<p>Olá,<br>
    Para votar no {DESCRICAO_PERIODO} - <b><a href='{LINK}'> acesse aqui</a></b>.<br>
    A votação ocorrerá no dia " . strftime('%d de %B de %Y (%A)', strtotime($periodo["inicio"])) . " 
    no horário das " . date('H:i', strtotime($periodo["inicio"])) . " às " . date('H:i', strtotime($periodo["fim"])) . ". <br>
    O presente e-mail é de <b>USO PESSOAL</b> e <b>INTRANSFERÍVEL</b>. <br>
    </p>";

    $CORPO_MENSAGEM = str_replace("{DESCRICAO_PERIODO}", $periodo["descricao"], $CORPO_MENSAGEM);

    if ($_ENV["PRODUCAO"] == "false") {
        $token = $eleitorObj->gerarToken("thalles.aguiar@unifebe.edu.br", $eleitor["periodo"], $_ENV["JWT_CODE"]);
        $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
        $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
        $emailObj->setDestinatario("thalles.aguiar@unifebe.edu.br");
        $emailObj::enviarEmail($emailObj);

        echo json_encode(["erro" => false, "msg" => "E-mails enviados", "eleitores" => $_ENV["PRODUCAO"]]);
        die;
    }

    $token = $eleitorObj->gerarToken($eleitor["email"], $eleitor["periodo"], $_ENV["JWT_CODE"]);
    $CORPO_MENSAGEM = str_replace("{LINK}", $_ENV["URL"] . "/index.php?token=" . rawurlencode($token), $CORPO_MENSAGEM);
    $emailObj->setCorpo_mensagem($CORPO_MENSAGEM);
    $emailObj->setDestinatario($eleitor["email"]);
    $emailObj::enviarEmail($emailObj);

    echo json_encode(["erro" => false, "msg" => "E-mails enviados", "eleitor" => $eleitor]);
}



call_user_func($_REQUEST["method"],  $request);
