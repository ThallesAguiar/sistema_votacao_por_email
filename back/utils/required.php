<?php
/**função que trata os valores que são obrigatórios não ser nulas ou vazias */
function required($value = null, $msg)
{
    if ($value === null || $value === "") {
        echo json_encode(["erro" => true, "msg" => $msg]);
        die();
    }
}
