<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : exit('Acceso no disponible');
$id_doc = $_POST['id_doc'];
$id_municipio = $_POST['id_municipio'];
$id_sede = $_POST['id_sede'];
$id_cc = $_POST['id_cc'];
$valor_cc = str_replace(',', '', $_POST['valor_cc']);
$iduser = $_SESSION['id_user'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_detalle == '0') {
        $sql = "INSERT INTO `ctb_causa_costos`
                    (`id_ctb_doc`,`id_area_cc`,`valor`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cc, PDO::PARAM_INT);
        $sql->bindParam(3, $valor_cc, PDO::PARAM_STR);
        $sql->bindParam(4, $iduser, PDO::PARAM_INT);
        $sql->bindParam(5, $fecha2, PDO::PARAM_STR);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `ctb_causa_costos`
                    SET `id_area_cc` = ?, `valor` = ?
                WHERE `id` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_cc, PDO::PARAM_INT);
        $sql->bindParam(2, $valor_cc, PDO::PARAM_STR);
        $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `ctb_causa_costos` SET `id_user_act` = ?,`fecha_act` = ? WHERE `id` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(1, $iduser, PDO::PARAM_INT);
                $sql->bindParam(2, $fecha2);
                $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
                $sql->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
$acumulado = GetValoresCxP($id_doc, $cmd);
$acumulado = $acumulado['val_ccosto'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
