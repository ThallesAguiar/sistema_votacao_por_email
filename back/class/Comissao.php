<?php

class Comissao
{
    public static function cadastrar($comissao)
    {
    }

    public static function buscar()
    {
    }
    public static function buscarPorId($id)
    {

        # code...
    }
    public static function atualizar($comissao)
    {

        # code...
    }
    public static function deletar($id)
    {

        # code...
    }

    /**Valida usuário na requesição. Retorna dados do usuário. */
    public static function autenticar($login, $senha, $instituicao)
    {

        $comissao = (array)json_decode(file_get_contents(dirname(__DIR__) . "/database/comissao.json"));

        $comissao = isset($comissao[$instituicao]) ? (array)$comissao[$instituicao] : null;

        if (!$comissao) {
            echo json_encode(["erro" => true, "msg" => "Instituição não existe em nosso banco de dados. Se o erro persistir, favor entrar em contato pelo e-mail meajuda@unifebe.edu.br"]);
            die;
        }
        $usuarioLogado = $login . $comissao["dominio"];

        $membro_ativo = null;

        /**Valida se o usuário é admin, senão ele deve ser da comissão, caso não for, retorna mensagem de erro. */
        foreach ($comissao["admin"] as $membro) {
            if (is_array($membro)) {
                foreach ($membro as $c => $m) {
                    if ($m->email == $usuarioLogado) {
                        $membro_ativo = $m;
                    }
                }
            } else {
                if ($membro->email == $usuarioLogado) {
                    $membro_ativo = $membro;
                }
            }
        }

        if (!$membro_ativo) {
            foreach ($comissao["membros_comissao"] as $membro) {
                if (is_array($membro)) {
                    foreach ($membro as $c => $m) {
                        if ($m->email == $usuarioLogado) {
                            $membro_ativo = $m;
                        }
                    }
                } else {
                    if ($membro->email == $usuarioLogado) {
                        $membro_ativo = $membro;
                    }
                }
            }
        }
        
        if(!$membro_ativo){
            echo json_encode(["erro" => true, "msg" => "Este usuário não faz parte da comissão eleitoral."]);
            die;
        }
        
        if($membro_ativo->senha !== md5($senha)){
            echo json_encode(["erro" => true, "msg" => "Erro na sua autenticação."]);
            die;
        }

        // echo json_encode($membro_ativo);
        return $membro_ativo;
    }
}
