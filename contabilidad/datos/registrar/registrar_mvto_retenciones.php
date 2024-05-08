<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$data = isset($_POST['factura_des']) ? explode('|', $_POST['factura_des']) : exit('Acceso no disponible');
$tipo_rete = $_POST['tipo_rete'];
$id_doc = $_POST['id_docr'];
$id_rete = $_POST['id_rete'];
$tarifa = $_POST['tarifa'] > 0 ? $_POST['tarifa'] : 0;
$id_terceroapi = $_POST['id_terceroapi'] > 0 ? $_POST['id_terceroapi'] : NULL;
$valor_rte = str_replace(",", "", $_POST['valor_rte']);
$base = str_replace(",", "", $data[0]);
$base_iva = str_replace(",", "", $data[1]);
$id_detalle = $_POST['id_detalle'];
$id_rango = $_POST['id_rango'] > 0 ? $_POST['id_rango'] : NULL;
$id_rete_sobre = isset($_POST['id_rete_sobre']) ? $_POST['id_rete_sobre'] : 0;
if ($tipo_rete == 2) {
    $base = $base_iva;
}
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
//
include '../../../conexion.php';
include '../../../permisos.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['status'] = 'error';
if ($id_detalle == 0) {
    try {
        $query = "INSERT INTO `ctb_causa_retencion`
                        (`id_ctb_doc`,`id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $id_rango, PDO::PARAM_INT);
        $query->bindParam(3, $base, PDO::PARAM_STR);
        $query->bindParam(4, $tarifa, PDO::PARAM_STR);
        $query->bindParam(5, $valor_rte, PDO::PARAM_STR);
        $query->bindParam(6, $id_terceroapi, PDO::PARAM_INT);
        $query->bindParam(7, $iduser, PDO::PARAM_INT);
        $query->bindValue(8, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            if ($id_rete_sobre > 0) {
                $base = explode('_', $_POST['id_rete_sede']);
                $base = $base[1];
                $sql = "SELECT `id_rango` FROM `ctb_retencion_rango` WHERE `id_retencion` = 45 LIMIT 1 ";
                $rs = $cmd->query($sql);
                $rango = $rs->fetch();
                $id_rango = !empty($rango['id_rango']) ? $rango['id_rango'] : 0;
                $query->execute();
            }
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } catch (PDOException $e) {
        $response['msg'] = $e->getMessage();
    }
} else {
}
$acumulado = GetValoresCxP($id_doc, $cmd);
$acumulado = $acumulado['val_retencion'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
