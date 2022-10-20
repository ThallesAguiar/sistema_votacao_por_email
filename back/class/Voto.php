<?php

class Voto
{
    private $chapa;
    private $categoria_eleitor;



    /**
     * Get the value of chapa
     */
    public function getChapa()
    {
        return $this->chapa;
    }

    /**
     * Set the value of chapa
     *
     * @return  self
     */
    public function setChapa($chapa)
    {
        $this->chapa = $chapa;

        return $this;
    }

    /**
     * Get the value of categoria_eleitor
     */
    public function getCategoria_eleitor()
    {
        return $this->categoria_eleitor;
    }

    /**
     * Set the value of categoria_eleitor
     *
     * @return  self
     */
    public function setCategoria_eleitor($categoria_eleitor)
    {
        $this->categoria_eleitor = $categoria_eleitor;

        return $this;
    }

    public static function cadastrar(Voto $voto)
    {
        global $conn;

        $sql = "INSERT INTO votos (chapa, categoria_eleitor) VALUES ('{$voto->getChapa()}','{$voto->getCategoria_eleitor()}')";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function buscar($periodo = null)
    {
        global $conn;

        $query = mysqli_query($conn, "SELECT COUNT(c.periodo) AS total FROM votos v LEFT JOIN chapas c ON c.id = v.chapa WHERE c.periodo = $periodo");
        $row = mysqli_fetch_assoc($query);
        $total_votos_no_periodo = $row['total'];



        $sql = "SELECT 
                    v.chapa, 
                    c.nome AS nome_chapa, 
                    v.categoria_eleitor, 
                    (CASE WHEN v.categoria_eleitor = 1 THEN 'professores' ELSE 
                    (CASE WHEN v.categoria_eleitor = 2 THEN 'alunos' ELSE 
                    (CASE WHEN v.categoria_eleitor = 3 THEN 'funcionarios' END) END) END) AS nome_categoria,
                    $total_votos_no_periodo AS total_votos_no_periodo,
                    (SELECT COUNT(chapa) FROM votos WHERE chapa = v.chapa ) AS total_votos_na_chapa,
                    COUNT(v.categoria_eleitor) AS total_votos_na_chapa_por_categoria, 
                    (SELECT COUNT(*) FROM votos vt LEFT JOIN chapas ch ON ch.id = vt.chapa WHERE ch.periodo = $periodo AND vt.categoria_eleitor = v.categoria_eleitor) AS total_votos_validos_por_categoria,
                    (COUNT(v.categoria_eleitor)*100) / $total_votos_no_periodo AS porcentagem_total_votos_por_chapa_e_categoria,
                    (SELECT (COUNT(categoria_eleitor)*100 /  $total_votos_no_periodo) FROM votos WHERE chapa = v.chapa) AS porcentagem_soma_total,
                    (CASE WHEN v.categoria_eleitor = 1 THEN (COUNT(v.categoria_eleitor) * 70) / (SELECT COUNT(*) FROM votos vt LEFT JOIN chapas ch ON ch.id = vt.chapa WHERE ch.periodo = $periodo AND vt.categoria_eleitor = v.categoria_eleitor) ELSE 
                    (CASE WHEN v.categoria_eleitor = 2 THEN (COUNT(v.categoria_eleitor) * 15) / (SELECT COUNT(*) FROM votos vt LEFT JOIN chapas ch ON ch.id = vt.chapa WHERE ch.periodo = $periodo AND vt.categoria_eleitor = v.categoria_eleitor) ELSE 
                    (CASE WHEN v.categoria_eleitor = 3 THEN (COUNT(v.categoria_eleitor) * 15) / (SELECT COUNT(*) FROM votos vt LEFT JOIN chapas ch ON ch.id = vt.chapa WHERE ch.periodo = $periodo AND vt.categoria_eleitor = v.categoria_eleitor) END) END) END) AS porcentagem_apuracao_eleitoral
                FROM votos v 
                LEFT JOIN chapas c
                ON c.id = v.chapa
                WHERE c.periodo = $periodo
                GROUP BY v.chapa, v.categoria_eleitor";

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        $votos = [];

        while ($row = mysqli_fetch_array($query, true)) {
            $votos[] = $row;            
        }


        return $votos;
    }
    public static function buscarPorId($id)
    {
        # code...
    }
    public static function atualizar(Voto $voto)
    {
        # code...
    }
    public static function deletar($id)
    {
        # code...
    }
    public static function registrarVotoNoEleitor($email, $periodo)
    {
        global $conn;

        date_default_timezone_set('America/Sao_Paulo');

        $hoje = date('Y-m-d H:i:s');

        $sql = "UPDATE eleitores
        SET data_votacao = '$hoje', votou = 1
        WHERE email = '$email' 
        AND periodo = $periodo";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function porcentagem_de_votos_validos_atribuidos_para_chapa($periodo)
    {
        global $conn;
        

    }
}
