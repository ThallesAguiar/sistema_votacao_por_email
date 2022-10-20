<?php

function formatarDataExtensoParaTexto($data)
{
    require_once(dirname(__DIR__) . "/utils/converterNumeroParaEscrita.php");

    setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    $ano =  strftime('%Y', strtotime($data));
    $mes =  strftime('%B', strtotime($data));
    $dia =  strftime('%d', strtotime($data));

    return converterNumeroParaEscrita(intval($dia)) . ' dias do mês de outubro do ano de ' . converterNumeroParaEscrita($ano);
    // return converterNumeroParaEscrita(intval($dia)) . ' dias do mês de ' . $mes . ' do ano de ' . converterNumeroParaEscrita($ano);
}

function formatarDataExtenso($data)
{
    setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');
    return strftime('%d de %B de %Y', strtotime($data));
}
