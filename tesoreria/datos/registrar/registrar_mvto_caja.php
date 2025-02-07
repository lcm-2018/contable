<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}

$id_doc = isset($_POST['id_ctb_doc']) ? $_POST['id_ctb_doc'] : exit('Acceso no disponible');
$data = $_POST['valor'];
$id_user = $_SESSION['id_user'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$response['status'] = 'error';
$cambios = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `tes_caja_mvto` WHERE `id_ctb_doc` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();

    $sql = "INSERT INTO `tes_caja_mvto`
                (`id_caja_rubros`,`id_ctb_doc`,`valor`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_rubro, PDO::PARAM_INT);
    $sql->bindParam(2, $id_doc, PDO::PARAM_INT);
    $sql->bindParam(3, $valor, PDO::PARAM_STR);
    $sql->bindParam(4, $id_user, PDO::PARAM_INT);
    $sql->bindParam(5, $fecha2, PDO::PARAM_STR);
    foreach ($data as $key => $value) {
        $id_rubro = $key;
        $valor = str_replace(',', '', $value);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $cambios++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    if ($cambios > 0) {
        $response['status'] = 'ok';
        $query = "SELECT SUM(`valor`) AS `val_imputacion` FROM `tes_caja_mvto` WHERE `id_ctb_doc` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->execute();
        $response['acumulado'] = $query->fetch(PDO::FETCH_ASSOC)['val_imputacion'];
    } else {
        $response['msg'] = 'No se realizaron cambios';
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}

echo json_encode($response);
