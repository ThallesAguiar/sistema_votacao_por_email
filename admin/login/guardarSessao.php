<?php
// if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
//     // Quando entrar nessa condição, significa que o usuário tentou acessar o link diretamente
//     header("Location: ../404.php?msg=Você não pode fazer esse tipo de coisa.");
//     die();
// }

session_start();
$_SESSION['instituicao'] = $_GET['instituicao'];
$_SESSION['nome'] = $_GET['nome'];
$_SESSION['email'] = $_GET['email'];
$_SESSION['permissao'] = $_GET['permissao'];
$_SESSION['periodo'] = $_GET['periodo'];
$_SESSION['token'] = $_GET['token'];


header('Location: ../resultado.php');