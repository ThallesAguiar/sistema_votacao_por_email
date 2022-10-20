<?php

class Candidato
{
    private $id;
    private $chapa;
    private $nome;
    private $cargo;


    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Get the value of nome
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of nome
     *
     * @return  self
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get the value of cargo
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set the value of cargo
     *
     * @return  self
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;

        return $this;
    }


    public static function cadastrar(Candidato $candidato)
    {
        global $conn;

        $sql = "INSERT INTO candidatos (nome, cargo, chapa) VALUES ('{$candidato->getNome()}','{$candidato->getCargo()}','{$candidato->getChapa()}')";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function buscar($periodo = null)
    {
        global $conn;

        $sql = "SELECT ca.id, ca.nome AS nome_candidato, ca.cargo, cp.nome AS nome_chapa, p.descricao AS descricao_periodo FROM candidatos ca 
                LEFT JOIN chapas cp
                ON ca.chapa = cp.id
                LEFT JOIN periodos p
                ON cp.periodo = p.id ";
                
        if ($periodo != null && $periodo != "") {
            $sql .= "WHERE periodo = $periodo ";
        }

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        $candidatos = [];

        while ($row = mysqli_fetch_array($query, true)) {
            $candidatos[] = $row;
        }

        return $candidatos;
    }

    public static function buscarPorId($id)
    {
        global $conn;

        $sql = "SELECT ca.id, ca.nome AS nome_candidato, ca.cargo, cp.nome AS nome_chapa, p.descricao AS descricao_periodo FROM candidatos ca 
                LEFT JOIN chapas cp
                ON ca.chapa = cp.id
                LEFT JOIN periodos p
                ON cp.periodo = p.id 
                WHERE ca.id = $id";
        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        return mysqli_fetch_assoc($query);
    }

    public static function atualizar(Candidato $candidato)
    {
        global $conn;

        $sql = "UPDATE candidatos
        SET chapa = '{$candidato->getChapa()}', nome = '{$candidato->getNome()}', cargo = '{$candidato->getCargo()}'
        WHERE id = {$candidato->getId()};";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function deletar($id)
    {
        global $conn;

        $sql = "DELETE FROM candidatos WHERE id = $id;";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }
}
