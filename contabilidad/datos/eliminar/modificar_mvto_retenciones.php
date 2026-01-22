<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../conexion.php';
include_once '../../../financiero/consultas.php';

$res = array();

$id = $_POST['id'];
//$id_doc = $_POST['id_doc'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $sql = "SELECT
                ctb_causa_retencion.id_causa_retencion
                , ctb_causa_retencion.id_ctb_doc
                , ctb_retencion_tipo.id_retencion_tipo
                , ctb_retencion_tipo.tipo
                , ctb_retenciones.id_retencion
                , ctb_retenciones.nombre_retencion
                , ctb_causa_retencion.valor_base
                , ctb_causa_retencion.tarifa
                , ctb_causa_retencion.valor_retencion
                , ctb_causa_retencion.id_terceroapi
            FROM
                ctb_causa_retencion
                LEFT JOIN ctb_retencion_rango ON (ctb_causa_retencion.id_rango = ctb_retencion_rango.id_rango)
                LEFT JOIN ctb_retenciones ON (ctb_retencion_rango.id_retencion = ctb_retenciones.id_retencion)
                LEFT JOIN ctb_retencion_tipo ON (ctb_retenciones.id_retencion_tipo = ctb_retencion_tipo.id_retencion_tipo)
            WHERE ctb_causa_retencion.id_causa_retencion = $id";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
 
    $res['mensaje'] = 'ok';
    $res['value'] = 'ok';
    $res['id'] = $id;
    $res['id_tipo_retencion'] = $retenciones[0]['id_retencion_tipo'];
    $res['id_retencion'] = $retenciones[0]['id_retencion'];
    $res['valor_retencion'] = $retenciones[0]['valor_retencion'];
    $cmd = null;
    echo json_encode($res);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}