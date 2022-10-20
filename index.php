<?php
require_once("./global/index.php");

$token = isset($_GET["token"]) ? $_GET["token"] : null;
if (!$token || $token == "") {
    header('Location: ./404.php?msg=Você precisa estar autenticado para votar.');
    die();
}

$url = BASEURL . "/endpoint/eleitor.php?method=show";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['token' => $token]);
$eleitor = json_decode(curl_exec($ch));

if ($eleitor->erro) {
    header('Location: ./404.php?msg=' . $eleitor->msg);
    die();
}

if ($eleitor->retorno->eleitor->votou == 1 || $eleitor->retorno->eleitor->data_votacao != null) {
    header('Location: ./404.php?msg=O seu voto já foi computado. Não é possível votar novamente.');
    die();
}

$url = BASEURL . "/endpoint/chapa.php?method=index";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['periodo' => $eleitor->retorno->periodo->id]);
$periodo = json_decode(curl_exec($ch));

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/custom.css">
    <title><?php echo $eleitor->retorno->periodo->descricao ?></title>
</head>

<body>
    <header>
        <div class="titulo">
            <h1><?php echo $eleitor->retorno->periodo->descricao ?></h1>
        </div>
    </header>
    <div class="container">
        <form method="post">
            <div class="container-text">
                <div>
                    <p>Escolha uma das seguintes opções:</p>
                </div>
                <div class="chapas">
                    <?php foreach ($periodo->chapas as $chapa) : ?>
                        <div>
                            <label class="opcoes">
                                <?php echo $chapa->nome ?> <br>
                                <?php if (isset($chapa->candidatos) && count($chapa->candidatos) > 0) : ?>
                                    <?php foreach ($chapa->candidatos as $candidato) : ?>
                                        <small>
                                            <?php echo $candidato->nome_candidato . ' - ' . $candidato->cargo  ?>
                                        </small><br>
                                    <?php endforeach ?>
                                <?php endif ?>
                                <input type="radio" value="<?php echo $chapa->id_chapa ?>" name="chapa" onclick="carregaFoto('<?php echo $chapa->foto ?>')">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="botao">
                <img id="foto-chapa" src="<?php if (isset($chapa->foto)) { echo $chapa->foto; }  ?>" >
                <button class="btn_loading" id="btn_confirma" type="submit" onclick="registrarVoto('<?php echo $token ?>')">
                    confirma
                </button>
                <button class="btn_loading" id="btn_confirma_loading" type="button" style="width: 150px ; height: 50px ; display: none;">
                    <img src="./assets/img/carregando.gif" style="width: 40px ; height: 20px ;" alt="LOADING">
                </button>
            </div>
        </form>
    </div>
    <footer>
        <img src="https://www.unifebe.edu.br/site/wp-content/uploads/logo-unifebe-horizontal-slogan-branco.png" alt="logo-branca-unifebe">
    </footer>
</body>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> -->
<script src="./assets/js/axios.js"></script>
<script>
    const baseURL = '<?php echo BASEURL ?>';
    
    function carregaFoto(foto) {

        $("#foto-chapa").attr("src",foto);

    }


    function registrarVoto(token) {
        event.preventDefault();
        const chapa = document.querySelector('input[name=chapa]:checked') ? document.querySelector('input[name=chapa]:checked').value : null;
        if (!chapa) {
            Swal.fire(
                '',
                `Selecione uma das opção`,
                'warning'
            );
            return false;
        }

        $('#btn_confirma').css('display', 'none');
        $('#btn_confirma_loading').css('display', 'inline');

        axios({
            method: "POST",
            url: `${baseURL}/endpoint/voto.php?method=store`,
            responseType: "json",
            data: {
                token,
                chapa,
            },
        }).then(function(response) {
            if (response.data.erro) {
                Swal.fire(
                    '',
                    `${response.data.msg}`,
                    'error'
                );
                $('#btn_confirma').css('display', 'inline');
                $('#btn_confirma_loading').css('display', 'none');
                return false;
            } else {
                Swal.fire(
                    'Seu voto foi salvo',
                    `${response.data.msg}`,
                    'success'
                ).then(r => {
                    window.location.replace("./agradecimento.php?resultado="+response.data.resultado);
                });
            }
        });

    }
</script>

</html>