<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once(dirname(__DIR__) . "/vendor/autoload.php");

class Email
{
    private $remetente = 'eleicoes@email.com';
    private $destinatario;
    private $assunto;
    private $corpo_mensagem;
    private $descricao;
    private $momento;


    /**
     * Get the value of remetente
     */
    public function getRemetente()
    {
        return $this->remetente;
    }

    /**
     * Set the value of remetente
     *
     * @return  self
     */
    // public function setRemetente($remetente)
    // {
    //     $this->remetente = $remetente;

    //     return $this;
    // }

    /**
     * Get the value of destinatario
     */
    public function getDestinatario()
    {
        return $this->destinatario;
    }

    /**
     * Set the value of destinatario
     *
     * @return  self
     */
    public function setDestinatario($destinatario)
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    /**
     * Get the value of assunto
     */
    public function getAssunto()
    {
        return $this->assunto;
    }

    /**
     * Set the value of assunto
     *
     * @return  self
     */
    public function setAssunto($assunto)
    {
        $this->assunto = $assunto;

        return $this;
    }

    /**
     * Get the value of corpo_mensagem
     */
    public function getCorpo_mensagem()
    {
        return $this->corpo_mensagem;
    }

    /**
     * Set the value of corpo_mensagem
     *
     * @return  self
     */
    public function setCorpo_mensagem($corpo_mensagem)
    {
        $this->corpo_mensagem = $corpo_mensagem;

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
     * Get the value of momento
     */
    public function getMomento()
    {
        return $this->momento;
    }

    /**
     * Set the value of momento
     *
     * @return  self
     */
    public function setMomento($momento)
    {
        $this->momento = $momento;

        return $this;
    }


    public static function salvar(Email $email)
    {
        global $conn;

        date_default_timezone_set('America/Sao_Paulo');
        $email->setMomento(date('Y-m-d H:i:s'));

        $sql = "INSERT INTO emails (remetente, descricao, destinatario, enviado) VALUES ";
        $sql .= "('{$email->getRemetente()}','{$email->getDescricao()}','{$email->getDestinatario()}', '{$email->getMomento()}')";
        mysqli_query($conn, $sql);
        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }
    }

    public static function enviarEmail(Email $email)
    {

        $mail = new PHPMailer();

        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'user@example.com';                     //SMTP username
        $mail->Password   = 'secret';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`


        $mail->setFrom('eleicoes@email.com', 'ELEIÇÕES ' . date("Y"));
        $mail->addAddress($email->getDestinatario(), 'Eleitor');
        $mail->addBCC('eleicoes@email.com', "Eleições " . date("Y"));
        $mail->isHTML(true);
        $mail->Subject = $email->getAssunto();
        $mail->Body = $email->getCorpo_mensagem();



        if ($mail->Send()) {
            $email->salvar($email);
            return true;
        }

        return false;
    }

    public static function jaRecebeuEmail($periodo_descricao, $email)
    {

        global $conn;


        $sql = "SELECT * FROM emails WHERE descricao LIKE'%$periodo_descricao%' AND destinatario = '$email'";
        $query = mysqli_query($conn, $sql);

        if (mysqli_error($conn)) {
            echo json_encode(["erro" => true, "msg" => mysqli_error($conn)]);
            die();
        }

        return mysqli_fetch_assoc($query);
    }
}
