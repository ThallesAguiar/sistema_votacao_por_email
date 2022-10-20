<?php
session_start();
require_once(dirname(__DIR__)."/global/index.php");
require_once(dirname(__DIR__)."/function/verifica_auth.php");
// var_dump($_SESSION["token"], $_SESSION["permissao"]);
// die;

verifica_auth($_SESSION["token"], $_SESSION["permissao"]);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>SISFEBE votação</title>
</head>

<body>
    <header>
        <div class="titulo">
            <h1>Gerar resultado da eleição</h1>
        </div>
    </header>
    <section class="container">
        <form method="post">
            <div class="container-text">
                <div>
                    <p>Escolha uma das seguintes opções:</p>
                </div>
                <div class="chapas">
                    <div class="form-group">
                        <label for="periodo">Selecione a eleição</label>
                        <select class="form-control" id="periodo" name="periodo">
                            <option value="8">Processo Eleitoral 2022 - Reitoria</option>
                            <option value="10">Processo Eleitoral 2022 - TESTE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="documento">Selecione o documento</label>
                        <select class="form-control" id="documento" name="documento">
                            <option value="ATA">ATA</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="instituicao">Instituíção</label>
                        <select class="form-control" id="instituicao" name="instituicao">
                            <option value="<?php echo $_SESSION["instituicao"] ?>"><?php echo $_SESSION["instituicao"] ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sala_apuracao">Informe a sala de apuração</label>
                        <input type="text" class="form-control" id="sala_apuracao" placeholder="ex.: sala D316" name="sala_apuracao">
                    </div>
                    <div class="form-group">
                        <label for="data">Informe a data <br> <small>Se não informar a data, será atribuido a data atual</small></label>
                        <input type="date" style="width: 150px;" class="form-control" id="data" placeholder="DD-MM-AAAA">
                    </div>
                    <div class="form-group">
                        <label for="horario">Informe o horário <br> <small>Se não informar o horário, será atribuido o horário atual</small></label>
                        <input type="time" style="width: 100px;" class="form-control" id="horario" placeholder="HH:MM">
                    </div>
                </div>
            </div>
            <div class="botao">
                <button class="btn_loading" id="btn_confirma" type="button" onclick="gerarAta()">
                    Gerar ATA
                </button>
            </div>
        </form>
    </section>
    <footer>
        <img src="https://www.unifebe.edu.br/site/wp-content/uploads/logo-unifebe-horizontal-slogan-branco.png" alt="logo-branca-unifebe">
    </footer>
</body>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/axios.js"></script>

<script>
    const baseURL = '<?php echo BASEURL ?>';
    
    function gerarAta() {
        const periodo = $('#periodo').val() ? $('#periodo').val() : null;
        const documento = $('#documento').val() ? $('#documento').val() : null;
        const sala_apuracao = $('#sala_apuracao').val() ? $('#sala_apuracao').val() : null;
        const data = $('#data').val() ? $('#data').val() : null;
        const horario = $('#horario').val() ? $('#horario').val() : null;
        const instituicao = $('#instituicao').val() ? $('#instituicao').val() : null;

        console.log(periodo, documento, sala_apuracao, data, horario, instituicao)

        axios({
            method: "POST",
            url: `${baseURL}/endpoint/relatorio.php?method=gerarApuracaoATA`,
            // responseType: 'json',
            responseType: 'blob',
            data: {
                periodo,
                documento,
                sala_apuracao,
                data,
                horario,
                instituicao
            },
        }).then(function(response) {
            console.log(response)
            if (response.data.erro) {
                Swal.fire(
                    'pera ai',
                    `${response.data.msg}`,
                    'error'
                );
                $('#btn_confirma').css('display', 'inline');
                $('#btn_confirma_loading').css('display', 'none');
                return false;
            } else {
                // create file link in browser's memory
                const href = URL.createObjectURL(response.data);

                // create "a" HTLM element with href to file & click
                const link = document.createElement('a');
                link.href = href;
                link.setAttribute('download', 'ATA.docx'); //or any other extension
                document.body.appendChild(link);
                link.click();

                // clean up "a" element & remove ObjectURL
                document.body.removeChild(link);
                URL.revokeObjectURL(href);
            }
        });
    }
</script>

</html>