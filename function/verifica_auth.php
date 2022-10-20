<?php

function verifica_auth($token = null, $permissao = null)
{
    /**PARA FUNCIONAR ESTA FUNÇÃO, DEVE IMPORTAR PRIMEIRAMENTE O GLOBAL/INDEX.PHP NA PAGINA QUE QUER FAZER A VALIDAÇÃO */
    
    $url = BASEURL . "/endpoint/sessao.php?method=validar";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['token' => $token]);
    $auth = json_decode(curl_exec($ch));
    
    $tem_permissao = [1, 2, 3, 4, 77];
    
    $path_root = explode("/back", BASEURL);

    if ($auth->erro || !in_array($permissao, $tem_permissao)) {
        header("Location: ".$path_root[0]."/admin/login");
        // header("Location: ".$path_root[0]."/404.php?msg=Você não tem essa permissão.");
        die();
    }
}
