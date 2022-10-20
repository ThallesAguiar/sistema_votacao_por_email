<?php

class Relatorio
{
    public static function cadastrarModelo($nome_relatorio = "relatório", $upload_dir, $pasta_destino, $file, $extensao)
    {
        // upload_dir = C:\xampp\htdocs\votacao\back\docs\modelos
        // nome_relatorio = Eleicao_Reitoria_2022\ATA(1).doc

        var_dump($file);die;

        if (is_dir($upload_dir)) {
            // echo "A Pasta não Existe"; CRIA A SUBPASTA que é o nome do documento enviado
            if (is_dir($upload_dir . DIRECTORY_SEPARATOR . $pasta_destino)) {
                file_put_contents("$upload_dir" . DIRECTORY_SEPARATOR . "$pasta_destino" . DIRECTORY_SEPARATOR . "$nome_relatorio.$extensao", $file['file']['name'], true);
                return ['erro' => false, 'msg' => 'Arquivo salvo com sucesso'];
            } else {
                mkdir($upload_dir . DIRECTORY_SEPARATOR . $pasta_destino, 0755);
                file_put_contents("$upload_dir" . DIRECTORY_SEPARATOR . "$pasta_destino" . DIRECTORY_SEPARATOR . "$nome_relatorio.$extensao", $file['file']['name'], true);
                return ['erro' => false, 'msg' => 'Arquivo salvo com sucesso'];
            }
        } else {
            // echo "A Pasta não Existe"; CRIA A PASTA
            mkdir($upload_dir, 0755);

            // echo "A Pasta não Existe"; CRIA A SUBPASTA que é o nome do documento enviado
            if (!is_dir($upload_dir . DIRECTORY_SEPARATOR . $pasta_destino)) {
                mkdir($upload_dir . DIRECTORY_SEPARATOR . $pasta_destino, 0755);
                file_put_contents("$upload_dir" . DIRECTORY_SEPARATOR . "$pasta_destino" . DIRECTORY_SEPARATOR . "$nome_relatorio.$extensao", $file['file']['name'], true);
                return ['erro' => false, 'msg' => 'Arquivo salvo com sucesso'];
            }
        }
    }

    public static function buscar()
    {
    }

    public static function buscarPorId()
    {
    }

    public static function atualizar()
    {
    }

    public static function deletar()
    {
    }
}
