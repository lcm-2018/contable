<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
$_post = json_decode(file_get_contents('php://input'), true);
include_once '../../../conexion.php';
include_once '../../../financiero/consultas.php';

$id_doc = $_post['id_doc'];
$id_rad = $_post['id_rad'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['status'] = 'error';
$response['msg'] = '<br>Ningún registro afectado';
$datosDoc = GetValoresCxP($id_doc, $cmd);
try {
    $query = "SELECT `id_ctb_libaux` FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    if (!empty($datos)) {
        $query = $cmd->prepare("DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?");
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->execute();
    }
    $id_tercero = $datosDoc['id_tercero'];
    $id_tercero_ant =  $id_tercero;
    $query = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_referencia`.`id_cuenta`
                , `ctb_referencia`.`id_cta_credito`
                , IFNULL(`valores`.`valor_rad`,0) AS `valor_rad`
                , IFNULL(`causado`.`val_causado`,0) AS `val_causado`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_referencia` 
                    ON (`ctb_doc`.`id_ref_ctb` = `ctb_referencia`.`id_ctb_referencia`)
                LEFT JOIN 
                (SELECT
                    `id_pto_rad`
                    , SUM(IFNULL(`valor`,0) -IFNULL(`valor_liberado`,0)) AS `valor_rad` 
                FROM
                    `pto_rad_detalle`
                WHERE `id_pto_rad` = $id_rad
                ) AS `valores`
                    ON (`ctb_doc`.`id_rad` = `valores`.`id_pto_rad`)
                LEFT JOIN
                (SELECT
                    `ctb_doc`.`id_rad`
                    , SUM(IFNULL(`ctb_libaux`.`debito`,0)) AS `val_causado`
                FROM
                    `ctb_libaux`
                    INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                WHERE (`ctb_doc`.`id_rad` = $id_rad))AS `causado`
                ON (`ctb_doc`.`id_rad` = `causado`.`id_rad`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($query);
    $datos = $rs->fetch();

    $credito = 0;
    $ref = 0;
    $query = "INSERT INTO `ctb_libaux`
	            (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`,`ref`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $query->bindParam(4, $debito, PDO::PARAM_STR);
    $query->bindParam(5, $credito, PDO::PARAM_STR);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2);
    $query->bindParam(8, $ref, PDO::PARAM_INT);
    $valor = $datos['valor_rad'] - $datos['val_causado'];
    $debito = $valor;
    $id_cuenta = $datos['id_cuenta'];
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        $debito = 0;
        $credito = $valor;
        $id_cuenta = $datos['id_cta_credito'];
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] = 'Error al insertar el registro:' . $query->errorInfo()[2];
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
$cmd = null;
echo json_encode($response);
exit();
