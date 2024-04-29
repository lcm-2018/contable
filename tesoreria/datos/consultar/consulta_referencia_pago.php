<?php
session_start();
// Realiza la suma del valor total asignado a un CDP
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consulto si exite referencia de pago activa
try {
    $sql = "SELECT
                numero
            FROM
                tes_referencia
            WHERE estado =0;";
    $rs = $cmd->query($sql);
    $referencia = $rs->fetch();
    // verifico si la respuesta en mayor a 0
    if ($rs->rowCount() > 0) {
        $ref = $referencia['numero'];
        $response[] = array("value" => 'ok', "tipo" => 1, "num_ref" => $ref);
    } else {
        // Genero una nueva referencia de pago
        try {
            $sql = "SELECT
                MAX(numero) as valor
            FROM
                tes_referencia
           ";
            $rs = $cmd->query($sql);
            $referencia = $rs->fetch();
            $ref = $referencia['valor'] + 1;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $estado = 0;
        // Realizo un insert en la tabla tes_referencia donde numero = ref
        $query = $cmd->prepare("INSERT INTO tes_referencia (numero, estado,id_user_reg, fec_reg) VALUES (?, ?, ?, ?)");
        $query->bindParam(1, $ref, PDO::PARAM_INT);
        $query->bindParam(2, $estado, PDO::PARAM_INT);
        $query->bindParam(3, $iduser, PDO::PARAM_INT);
        $query->bindParam(4, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "tipo" => 2, "num_ref" => $ref);
        } else {
            print_r($query->errorInfo()[2]);
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
$cmd = null;

exit;
