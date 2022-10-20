<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/custom.css">
    <title>SISFEBE votação</title>
</head>

<body>
    <header>
        <div class="titulo">
            <h1>OBRIGADO!</h1>
        </div>
    </header>
    <section class="container">
        <div class="container-text">
            <div class="chapas">
                <label class="">
                    <?php echo $_GET["resultado"] ?>
                </label>
            </div>
        </div>
    </section>
    <footer>
        <img src="https://www.unifebe.edu.br/site/wp-content/uploads/logo-unifebe-horizontal-slogan-branco.png" alt="logo-branca-unifebe">
    </footer>
</body>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        function disableBack() {
            window.history.forward()
        }
        window.onload = disableBack();
        window.onpageshow = function(e) {
            if (e.persisted)
                disableBack();
        }
    });
</script>

</html>