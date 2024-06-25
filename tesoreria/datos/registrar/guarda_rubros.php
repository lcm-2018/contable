<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_detalle = isset($_POST['id_caja_rubros']) ? $_POST['id_caja_rubros'] : exit('Acceso no disponible');
$id_caja = $_POST['id_caja'];
$concepto = $_POST['slcConcepto'];
$valor = $_POST['numValor'];
$rubro = $_POST['id_rubroCod'];
$cuenta = $_POST['id_codigoCta'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_detalle == 0) {
        $query = "INSERT INTO `tes_caja_rubros`
                    (`id_caja_const`,`id_rubro_gasto`,`id_cta_contable`,`id_caja_concepto`,`valor`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_caja, PDO::PARAM_INT);
        $query->bindParam(2, $rubro, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta, PDO::PARAM_INT);
        $query->bindParam(4, $concepto, PDO::PARAM_INT);
        $query->bindParam(5, $valor, PDO::PARAM_STR);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_caja_rubros`
                    SET `id_rubro_gasto` = ?, `id_cta_contable` = ?, `id_caja_concepto` = ?, `valor` = ?
                WHERE `id_caja_rubros` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $rubro, PDO::PARAM_INT);
        $query->bindParam(2, $cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $concepto, PDO::PARAM_INT);
        $query->bindParam(4, $valor, PDO::PARAM_STR);
        $query->bindParam(5, $id_detalle, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2] . $query->queryString;
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `tes_caja_rubros` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_caja_rubros` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id_detalle, PDO::PARAM_INT);
                $query->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
