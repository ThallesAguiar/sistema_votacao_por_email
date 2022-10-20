<?php

// AUTOLOAD
spl_autoload_register(function ($nameClass) {
    //para cuidar da barra \ , no MAC e LINUX
    $nameClass = str_replace('\\', DIRECTORY_SEPARATOR, $nameClass);

    // Indicando onde o php irá procurar nossas classes
    $dirClass = dirname(__DIR__).DIRECTORY_SEPARATOR.'class';
    $filename = $dirClass . DIRECTORY_SEPARATOR . $nameClass . '.php';
    // $pathName

    if (file_exists($filename)) {
        require_once($filename);
    }
});