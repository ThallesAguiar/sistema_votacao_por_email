<?php

class Chapa
{
    private $id;
    private $nome;
    private $periodo;
    private $foto;


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
     * Get the value of periodo
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set the value of periodo
     *
     * @return  self
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get the value of foto
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set the value of foto
     *
     * @return  self
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;

        return $this;
    }

    public static function cadastrar(Chapa $chapa)
    {
        global $conn;

        $sql = "INSERT INTO chapas (nome, periodo, foto) VALUES ('{$chapa->getNome()}','{$chapa->getPeriodo()}','{$chapa->getFoto()}')";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function buscar($periodo)
    {
        global $conn;

        $sql = "SELECT ch.id AS id_chapa, ch.nome, ch.periodo, ch.foto FROM chapas ch WHERE ch.periodo = $periodo";

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        $chapas["chapa"] = [];
        $c = [];

        while ($row = mysqli_fetch_array($query, true)) {
            $chapas["chapa"][] = $row;
        }

        foreach ($chapas["chapa"] as $chapa) {
            $sql_cand = "SELECT ca.id AS id_candidato, ca.nome AS nome_candidato, ca.cargo 
            FROM candidatos ca WHERE ca.chapa = {$chapa['id_chapa']}";

            $query_cand = mysqli_query($conn, $sql_cand);

            while ($row_cand = mysqli_fetch_array($query_cand, true)) {
                $chapa["candidatos"][] = $row_cand;
            }

            $c[] = $chapa;
        }

        return $c;
    }

    public static function buscarPorId($id)
    {
        global $conn;

        $sql = "SELECT ch.id, ch.nome, ch.periodo, ch.foto, ca.id AS id_candidato, ca.nome AS nome_candidato, ca.cargo 
        FROM chapas ch 
        LEFT JOIN candidatos ca 
        ON ch.id = ca.chapa
        WHERE ch.id = $id";

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        $chapa = null;
        while ($row = mysqli_fetch_array($query, true)) {
            $chapa["chapa"] = [
                "id" => $row["id"],
                "nome" => $row["nome"],
                "periodo" => $row["periodo"],
                "foto" => $row["foto"],
            ];

            $chapa["candidatos"][] = [
                "id_candidato" => $row["id_candidato"],
                "nome_candidato" => $row["nome_candidato"],
                "cargo" => $row["cargo"],
            ];
        }

        return $chapa;
    }

    public static function atualizar(Chapa $chapa)
    {
        global $conn;

        $sql = "UPDATE chapas
        SET nome = '{$chapa->getNome()}', periodo = '{$chapa->getPeriodo()}', foto = '{$chapa->getFoto()}'
        WHERE id = {$chapa->getId()};";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function deletar($id)
    {
        global $conn;

        $sql = "DELETE FROM chapas WHERE id = $id;";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }
}
