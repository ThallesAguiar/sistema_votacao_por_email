<?php

class Eleitor
{
    private $categoria;
    private $email;
    private $periodo;
    private $data_votacao;
    private $votou;



    /**
     * Get the value of categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set the value of categoria
     *
     * @return  self
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Get the value of data_votacao
     */
    public function getData_votacao()
    {
        return $this->data_votacao;
    }

    /**
     * Set the value of data_votacao
     *
     * @return  self
     */
    public function setData_votacao($data_votacao)
    {
        $this->data_votacao = $data_votacao;

        return $this;
    }

    /**
     * Get the value of votou
     */
    public function getVotou()
    {
        return $this->votou;
    }

    /**
     * Set the value of votou
     *
     * @return  self
     */
    public function setVotou($votou)
    {
        $this->votou = $votou;

        return $this;
    }

    public static function cadastrar(Eleitor $eleitor)
    {
        global $conn;

        $sql = "INSERT INTO eleitores (categoria, email, periodo) VALUES ";
        $sql .= "({$eleitor->getCategoria()},'{$eleitor->getEmail()}',{$eleitor->getPeriodo()})";

        mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function total($periodo)
    {
        global $conn;

        $total = null;
        /**CONTA OS ELEITORES DO PERIODO */
        $sql = "SELECT COUNT(*) AS total_eleitores FROM eleitores WHERE periodo = '$periodo'";
        $query = mysqli_query($conn, $sql);
        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
        $total = mysqli_fetch_assoc($query);


        /**CONTA OS ELEITORES DO PERIODO */
        $sql = "SELECT
                    (CASE WHEN e.categoria = 1 THEN 'professores' ELSE 
                    (CASE WHEN e.categoria = 2 THEN 'alunos' ELSE 
                    (CASE WHEN e.categoria = 3 THEN 'funcionarios' END) END) END) AS nome_categoria,
                    COUNT(e.categoria) AS total_inscritos_por_categoria,
                    (SELECT COUNT(categoria) AS votos FROM eleitores WHERE periodo = $periodo AND votou = 1) AS total_votantes_presentes,
                    (SELECT COUNT(categoria) AS votos FROM eleitores WHERE periodo = $periodo AND votou = 0) AS total_votantes_ausentes,
                    (SELECT COUNT(categoria) AS votos FROM eleitores WHERE periodo = $periodo AND votou = 1 AND categoria = e.categoria) AS total_presentes_por_categoria,
                    (SELECT COUNT(categoria) AS votos FROM eleitores WHERE periodo = $periodo AND votou = 0 AND categoria = e.categoria) AS total_ausentes_por_categoria
                FROM eleitores e 
                WHERE periodo = $periodo 
                GROUP BY e.categoria
                ORDER BY nome_categoria";
        $query = mysqli_query($conn, $sql);
        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
        while ($users = mysqli_fetch_array($query, true)) {
            $total["votantes"] = [                
                "total_votantes_presentes"=> $users["total_votantes_presentes"],
                "total_votantes_ausentes"=> $users["total_votantes_ausentes"],
            ];
            $total["categorias"][] = [
                "nome_categoria"=> $users["nome_categoria"],
                "total_inscritos_por_categoria"=> $users["total_inscritos_por_categoria"],
                "total_presentes_por_categoria"=> $users["total_presentes_por_categoria"],
                "total_ausentes_por_categoria"=> $users["total_ausentes_por_categoria"]
            ];
        }

        return $total;
    }


    public function validar($token, $JWT_CODE)
    {
        $part = explode(".", $token);
        $header = $part[0];
        $payload = $part[1];
        $signature = $part[2];

        $valid = hash_hmac('sha256', "$header.$payload", $JWT_CODE, true);
        $valid = base64_encode($valid);


        if ($signature != $valid) {
            return ["erro" => true, "msg" => "Autenticação inválida", "payload" => null];
        }

        $payload = base64_decode($payload);
        $payload = json_decode($payload);

        return ["erro" => false, "msg" => "validado", "payload" => $payload];
    }

    public function buscarPorEmail($email, $periodo = null)
    {
        global $conn;

        $sql = "SELECT * FROM eleitores WHERE email = '$email'";

        if ($periodo) {
            $sql .= " AND periodo = $periodo";
        }

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        return mysqli_fetch_assoc($query);
    }

    public function buscarPorPeriodo($periodo, $categoria = null, $votou = null)
    {
        global $conn;

        $eleitores = [];

        $sql = "SELECT * FROM eleitores WHERE periodo = '$periodo'";

        if ($categoria) {
            $sql .= " AND categoria IN ($categoria)";
        }
        if ($votou) {
            $sql .= " AND votou = $votou";
        }

        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        while ($users = mysqli_fetch_array($query, true)) {
            $eleitores[] = $users;
        }

        return $eleitores;
    }

    function gerarToken($email, $periodo, $JWT_CODE)
    {

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $header = json_encode($header);
        $header = base64_encode($header);

        $payload = [
            'email' => $email,
            'periodo' => $periodo
        ];
        $payload = json_encode($payload);
        $payload = base64_encode($payload);

        $signature = hash_hmac('sha256', "$header.$payload", $JWT_CODE, true);
        $signature = base64_encode($signature);

        $token = "$header.$payload.$signature";


        return $token;
    }
}
