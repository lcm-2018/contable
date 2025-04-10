<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
$id_doc = $_POST['id'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$valida = true;
try {
    $sql = "SELECT `id_cuenta` FROM `ctb_libaux` WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    foreach ($cuentas as $c) {
        if ($c['id_cuenta'] == '') {
            $valida = false;
            break;
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response['res'] = 'error';
$datosDoc = GetValoresCxP($id_doc, $cmd);
if ($_SESSION['caracter'] == '1' && $_SESSION['pto'] == '0') {
    $datosDoc['val_imputacion'] = $datosDoc['val_factura'];
}
if ($datosDoc['val_factura'] == $datosDoc['val_imputacion'] && $datosDoc['val_factura'] == $datosDoc['val_ccosto'] && $valida) {
    $response['res'] = 'ok';
}
echo json_encode($response);
