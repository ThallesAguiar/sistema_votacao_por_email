<?php
require_once(dirname(__DIR__) . "/vendor/autoload.php");

// Inicia o DOTENV
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"], $_ENV["DB_DATABASE"]);

if ($conn->connect_errno) {
    echo '<p> Erro ' . $conn->errno . '-->' . $conn->connect_error . '</p>';
    die();
}

mysqli_set_charset($conn, "utf8");
