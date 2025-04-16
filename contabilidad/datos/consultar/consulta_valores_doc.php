<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
$id_doc = $_POST['id'];
$tipo = $_POST['tipo'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    if ($id_doc == 0) {
        $inicia = $_POST['docInicia'];
        $termina = $_POST['docTermina'];
        $tp = $_POST['tipo'];

        $sql = "SELECT 
                    `id_ctb_doc`, `id_manu`
                FROM
                    `ctb_doc`
                WHERE (`id_manu` BETWEEN '$inicia' AND '$termina'AND `id_tipo_doc` = $tp AND `estado` > 0)";
    } else {
        $sql = "SELECT 
                    `id_ctb_doc`, `id_manu`
                FROM
                    `ctb_doc`
                WHERE (`id_ctb_doc` = $id_doc)";
    }
    $rs = $cmd->query($sql);
    $datas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cadena = [];
foreach ($datas as $doc) {
    $id = $doc['id_ctb_doc'];
    $cadena[] = $id;
    $id_manu = $doc['id_manu'];
    $valida = true;
    if ($tipo == '3') {
        try {
            $sql = "SELECT `id_cuenta` FROM `ctb_libaux` WHERE (`id_ctb_doc` = $id)";
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
        $datosDoc = GetValoresCxP($id, $cmd);
        if ($_SESSION['caracter'] == '1' && $_SESSION['pto'] == '0') {
            $datosDoc['val_imputacion'] = $datosDoc['val_factura'];
        }
        if ($datosDoc['val_factura'] == $datosDoc['val_imputacion'] && $datosDoc['val_factura'] == $datosDoc['val_ccosto'] && $valida) {
            $response['res'] = 'ok';
            $response['msg'] = $cadena;
        } else {
            $response['res'] = 'error';
            $response['msg'] = $doc['id_manu'];
            break;
        }
    } else {
        $response['res'] = 'ok';
        $response['msg'] = $cadena;
    }
}
echo json_encode($response);
