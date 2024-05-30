<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_ctb_doc = isset($_POST['id_pag_doc']) ? $_POST['id_pag_doc'] : exit('Accion no permitida');
$ids_cops = $_POST['detalle'];
$tercero = $_POST['id_tercero'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$liberado = 0;
$response['status'] = 'error';
$response['msg'] = '';
$registros = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `pto_pag_detalle`
                (`id_ctb_doc`,`id_pto_cop_det`,`valor`,`valor_liberado`,`id_tercero_api`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($sql);
    $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
    $query->bindParam(2, $id_detalle, PDO::PARAM_INT);
    $query->bindParam(3, $val_pag, PDO::PARAM_STR);
    $query->bindParam(4, $liberado, PDO::PARAM_STR);
    $query->bindParam(5, $tercero, PDO::PARAM_INT);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2, PDO::PARAM_STR);
    foreach ($ids_cops as $key => $value) {
        $id_detalle = $key;
        $val_pag = str_replace(",", "", $value);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $registros++;
        } else {
            $response['msg'] += $query->errorInfo()[2] . '<br>';
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $sql = "SELECT SUM(`valor`) AS `valor` FROM `pto_pag_detalle` WHERE `id_ctb_doc` = $id_ctb_doc";
    $rs = $cmd->query($sql);
    $valor = $rs->fetch();
    $response['status'] = 'ok';
    $response['valor'] = $valor['valor'];
}
echo json_encode($response);
