<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$id_ctb_doc = isset($_POST['id_doc'])  ? $_POST['id_doc'] : exit('Acceso no disponible');
$id_detalle = isset($_POST['id_detalle'])  ? $_POST['id_detalle'] : 0;
$id_banco = $_POST['banco'];
$id_pto_cop = $_POST['id_pto_cop'];
$cuenta_banco = $_POST['cuentas'];
$forma_pago = $_POST['forma_pago_det'];
$documento = $_POST['documento'];
$valor_pag = str_replace(",", "", $_POST['valor_pag']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$response['status'] = 'error';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $query = "INSERT INTO `tes_detalle_pago`
                    (`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`id_user_reg`,`fecha_reg`)
                VALUES(?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
    $query->bindParam(2, $cuenta_banco, PDO::PARAM_INT);
    $query->bindParam(3, $forma_pago, PDO::PARAM_STR);
    $query->bindParam(4, $documento, PDO::PARAM_INT);
    $query->bindParam(5, $valor_pag, PDO::PARAM_STR);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2, PDO::PARAM_STR);
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        /*
        $sql = "SELECT
                    `id_chequera`
                FROM
                    `seg_fin_chequeras`
                WHERE `id_cuenta` = $cuenta_banco AND `estado` = 1";
        $rs = $cmd->query($sql);
        $cheques = $rs->fetch();
        $id_chequera = $cheques['id_chequera'];
        $query = $cmd->prepare("INSERT INTO seg_fin_chequera_cont (id_chequera,contador) VALUES (?, ?)");
        $query->bindParam(1, $id_chequera, PDO::PARAM_INT);
        $query->bindParam(2, $documento, PDO::PARAM_INT);
        $query->execute();
        */
        $response['status'] = 'ok';
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}

echo json_encode($response);