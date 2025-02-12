<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$id_cta_factura = $_POST['id_cta_factura'];
$id_ctb_doc = $_POST['id_doc'];
$id_tipo_doc = $_POST['tipoDoc'];
$fecha_fact = $_POST['fechaDoc'];
$fecha_ven = $_POST['fechaVen'];
$num_doc = $_POST['numFac'];
$valor_pago = str_replace(",", "", $_POST['valor_pagar']);
$valor_iva = str_replace(",", "", $_POST['valor_iva']);
$valor_base = str_replace(",", "", $_POST['valor_base']);
$detalle = mb_strtoupper($_POST['detalle']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$response['status'] = 'error';

if ($id_cta_factura == 0) {
    try {
        $sql = "INSERT INTO `ctb_factura`
                (`id_ctb_doc`,`id_tipo_doc`,`num_doc`,`fecha_fact`,`fecha_ven`,`valor_pago`,`valor_iva`,`valor_base`,`detalle`,`id_user_reg`,`fec_rec`)
            VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
        $sql->bindParam(2, $id_tipo_doc, PDO::PARAM_INT);
        $sql->bindParam(3, $num_doc, PDO::PARAM_INT);
        $sql->bindParam(4, $fecha_fact, PDO::PARAM_STR);
        $sql->bindParam(5, $fecha_ven, PDO::PARAM_STR);
        $sql->bindParam(6, $valor_pago, PDO::PARAM_STR);
        $sql->bindParam(7, $valor_iva, PDO::PARAM_STR);
        $sql->bindParam(8, $valor_base, PDO::PARAM_STR);
        $sql->bindParam(9, $detalle, PDO::PARAM_STR);
        $sql->bindParam(10, $iduser, PDO::PARAM_INT);
        $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
            $response['msg'] = 'Proceso realizado con correctamente';
        } else {
            $response['msg'] = 'Error: ' . $sql->errorInfo()[2];
        }
    } catch (PDOException $e) {
        $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else {
    try {
        $num_doc = $_POST['numFac'];
        $sql = "UPDATE `ctb_factura`
                    SET `id_tipo_doc` = ?, `num_doc` = ?, `fecha_fact` = ?, `fecha_ven` = ?, `valor_pago` = ?, `valor_iva` = ?, `valor_base` = ?, `detalle` = ?
                WHERE `id_cta_factura` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tipo_doc, PDO::PARAM_INT);
        $sql->bindParam(2, $num_doc, PDO::PARAM_INT);
        $sql->bindParam(3, $fecha_fact, PDO::PARAM_STR);
        $sql->bindParam(4, $fecha_ven, PDO::PARAM_STR);
        $sql->bindParam(5, $valor_pago, PDO::PARAM_STR);
        $sql->bindParam(6, $valor_iva, PDO::PARAM_STR);
        $sql->bindParam(7, $valor_base, PDO::PARAM_STR);
        $sql->bindParam(8, $detalle, PDO::PARAM_STR);
        $sql->bindParam(9, $id_cta_factura, PDO::PARAM_INT);
        if (!($sql->execute())) {
            $response['msg'] = $sql->errorInfo()[2];
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `ctb_factura` SET `id_user_act` = ?,`fec_act` = ? WHERE `id_cta_factura` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_cta_factura, PDO::PARAM_INT);
                $sql->execute();
                $response['status'] = 'ok';
                $response['msg'] = 'Proceso realizado con correctamente';
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    } catch (PDOException $e) {
        $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$acumulado = GetValoresCxP($id_ctb_doc, $cmd);
$acumulado = $acumulado['val_factura'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
