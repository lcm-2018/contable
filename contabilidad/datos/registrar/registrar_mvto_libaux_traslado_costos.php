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

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$fini = $_post['fini'];
$ffin = $_post['ffin'];
$id_doc = $_post['id_doc'];
$id_tercero = $_post['idtercero'];

$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');


$response['status'] = 'error';
$response['msg'] = 'Sin cambios realizados';
$cont = 0;
try {
    //eliminar los movimientos anteriores
    $sql = "DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();

    $sql = "WITH 
                `v` AS  
                    (SELECT
                        LEFT(`ctb_pgcp`.`cuenta`, 4) AS `cuenta`,
                        SUM(IFNULL(`ctb_libaux`.`debito`,0) - IFNULL(`ctb_libaux`.`credito`,0)) AS `valor`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_pgcp` ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    WHERE 
                        `ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` BETWEEN '$fini' AND '$ffin' AND `ctb_pgcp`.`cuenta` LIKE '7%'
                    GROUP BY LEFT(`ctb_pgcp`.`cuenta`, 4))
            SELECT
                `cc`.`id_cta_costo`
                , `cc`.`id_cta_debito`
                , `cc`.`id_cta_credito` 
                , IFNULL(`v`.`valor`,0) AS `valor`
            FROM `ctb_cuenta_costo`  `cc`
                INNER JOIN `ctb_pgcp` ON (`cc`.`id_cta_costo` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `v` ON (`v`.`cuenta` = `ctb_pgcp`.`cuenta`)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $query = "INSERT INTO `ctb_libaux`
	            (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $query->bindParam(4, $debito, PDO::PARAM_STR);
    $query->bindParam(5, $credito, PDO::PARAM_STR);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2);

    if (!empty($cuentas)) {
        foreach ($cuentas as $cta) {
            $id_cuenta = $cta['id_cta_debito'];
            $debito = $cta['valor'];
            $credito = 0;
            if ($debito > 0) {
                $query->execute();
                if ($cmd->lastInsertId() > 0) {
                    $id_cuenta = $cta['id_cta_credito'];
                    $debito = 0;
                    $credito = $cta['valor'];
                    $query->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $cont++;
                    } else {
                        $response['msg'] = $query->errorInfo()[2];
                    }
                } else {
                    $response['msg'] = $query->errorInfo()[2];
                }
            }
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}

if ($cont > 0) {
    $response['status'] = 'ok';
}

echo json_encode($response);
exit();
