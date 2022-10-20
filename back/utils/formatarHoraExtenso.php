<?php

function formatarHoraExtensoParaTexto($hora)
{
	require_once(dirname(__DIR__) . "/utils/converterNumeroParaEscrita.php");

	$_hora = explode(':', $hora);
	if (sizeof($_hora) < 2) {
		return converterNumeroParaEscrita($hora);
	}
	$hora     = $_hora[0];
	$minuto = $_hora[1];
	$ret = converterNumeroParaEscrita($hora) . ' horas';
	if (intval($minuto) != '' && $minuto != "00") {
		$ret .= ' e ' . converterNumeroParaEscrita($minuto) . ' minutos';
	}
	return $ret;
}

function formatarHoraExtenso($hora)
{
	$_hora = explode(':', $hora);
	if (sizeof($_hora) < 2) {
		return $hora;
	}
	$hora 	= $_hora[0];
	$minuto = $_hora[1];
	$ret = $hora . ' horas';
	if (intval($minuto) != '' && $minuto != "00") {
		$ret .= ' e ' . $minuto . ' minutos';
	}
	return $ret;
}
