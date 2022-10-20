<?php

class Periodo
{
    private $id;
    private $descricao;
    private $inicio;
    private $fim;
    private $resultado;


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
     * Get the value of descricao
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set the value of descricao
     *
     * @return  self
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get the value of inicio
     */
    public function getInicio()
    {
        return $this->inicio;
    }

    /**
     * Set the value of inicio
     *
     * @return  self
     */
    public function setInicio($inicio)
    {
        $this->inicio = $inicio;

        return $this;
    }

    /**
     * Get the value of fim
     */
    public function getFim()
    {
        return $this->fim;
    }

    /**
     * Set the value of fim
     *
     * @return  self
     */
    public function setFim($fim)
    {
        $this->fim = $fim;

        return $this;
    }


    /**
     * Get the value of resultado
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Set the value of resultado
     *
     * @return  self
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;

        return $this;
    }

    public static function criarPeriodo(Periodo $periodo)
    {
        global $conn;

        $sql = "INSERT INTO periodos (descricao, inicio, fim, resultado) VALUES ('{$periodo->getDescricao()}','{$periodo->getInicio()}','{$periodo->getFim()}','{$periodo->getResultado()}')";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function atualizarPeriodo(Periodo $periodo)
    {
        global $conn;

        $sql = "UPDATE periodos
        SET descricao = '{$periodo->getDescricao()}', inicio = '{$periodo->getInicio()}', fim = '{$periodo->getFim()}', resultado = '{$periodo->getResultado()}'
        WHERE id = {$periodo->getId()};";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function deletarPeriodo($id)
    {
        global $conn;

        $sql = "DELETE FROM periodos WHERE id = $id;";
        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function buscarPeriodos()
    {
        global $conn;
        $periodos = [];

        $sql = "SELECT * FROM periodos";
        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        while ($row = mysqli_fetch_array($query, true)) {
            $inicio["data"] = date("d-m-Y", strtotime($row["inicio"]));
            $inicio["hora"] = date("H:i:s", strtotime($row["inicio"]));
            $row["inicio"] = $inicio;

            $fim["data"] = date("d-m-Y", strtotime($row["fim"]));
            $fim["hora"] = date("H:i:s", strtotime($row["fim"]));
            $row["fim"] = $fim;

            $resultado["data"] = date("d-m-Y", strtotime($row["resultado"]));
            $resultado["hora"] = date("H:i:s", strtotime($row["resultado"]));
            $row["resultado"] = $resultado;

            $periodos[] = $row;
        }

        return $periodos;
    }

    public function buscarPorId($id)
    {
        global $conn;

        $sql = "SELECT * FROM periodos WHERE id = $id";
        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        return mysqli_fetch_assoc($query);
    }

    /**Verifica se o periodo selecionado esta dentro do prazo de validade */
    public function verificaPrazo($inicio, $fim)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $hoje = date('Y-m-d H:i:s');

        if ((strtotime($hoje) >= strtotime($inicio)) && (strtotime($hoje) <= strtotime($fim))) {
            return true;
        }

        return false;
    }
}
