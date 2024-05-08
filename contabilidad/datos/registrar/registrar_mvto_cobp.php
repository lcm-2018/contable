<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}

$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no disponible');
$data = $_POST['valor'];
$id_user = $_SESSION['id_user'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$liberado = 0;
$response['status'] = 'error';
$cambios = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `pto_cop_detalle`
                (`id_ctb_doc`, `id_pto_crp_det`, `id_tercero_api`, `valor`, `valor_liberado`, `id_user_reg`, `fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->bindParam(2, $id_crp_det, PDO::PARAM_INT);
    $sql->bindParam(3, $id_tercero_api, PDO::PARAM_INT);
    $sql->bindParam(4, $valor, PDO::PARAM_STR);
    $sql->bindParam(5, $liberado, PDO::PARAM_STR);
    $sql->bindParam(6, $id_user, PDO::PARAM_INT);
    $sql->bindParam(7, $fecha2, PDO::PARAM_STR);
    $query = "UPDATE `pto_cop_detalle` SET `valor` = ? WHERE `id_pto_cop_det` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $valor, PDO::PARAM_STR);
    $query->bindParam(2, $id_cop_det, PDO::PARAM_INT);
    foreach ($data as $key => $value) {
        $ids = explode("-", $key);
        $valor = str_replace(',', '', $value);
        if ($ids[0] == '0') {
            $id_crp_det = $ids[1];
            $id_tercero_api = $ids[2];
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $cambios++;
            }
        } else {
            $id_cop_det = $ids[0];
            $query->execute();
            if ($query->rowCount() > 0) {
                $cambios++;
            }
        }
    }
    if ($cambios > 0) {
        $response['status'] = 'ok';
    } else {
        $response['msg'] = 'No se realizaron cambios';
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
$acumulado = GetValoresCxP($id_doc, $cmd);
$acumulado = $acumulado['val_imputacion'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
