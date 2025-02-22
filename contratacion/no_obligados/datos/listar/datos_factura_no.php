<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_fact_noobligado`.`id_facturano`
                , `tb_tipos_documento`.`codigo_ne` AS `tipo_documento`
                , `tb_terceros`.`nit_tercero` AS `no_doc`
                , `tb_terceros`.`nom_tercero` AS `nombre`
                , `ctt_fact_noobligado`.`fec_compra`
                , `ctt_fact_noobligado`.`fec_vence`
                , `ctt_fact_noobligado`.`met_pago`
                , `ctt_fact_noobligado`.`forma_pago`
                , `nom_metodo_pago`.`metodo` AS `form_pago`
                , `ctt_fact_noobligado`.`vigencia`
                , `ctt_fact_noobligado`.`estado`
            FROM
                `ctt_fact_noobligado`
                LEFT JOIN `tb_terceros` 
                    ON (`ctt_fact_noobligado`.`id_tercero_no` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `nom_metodo_pago` 
                    ON (`ctt_fact_noobligado`.`forma_pago` = `nom_metodo_pago`.`id_metodo_pago`)
                LEFT JOIN `tb_tipos_documento` 
                    ON (`tb_terceros`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE `ctt_fact_noobligado`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $facturas_no = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids = [];
foreach ($facturas_no as $fno) {
    $ids[] = $fno['id_facturano'];
}
$ids = implode(',', $ids);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_factura_no`,`shash`,`referencia` 
            FROM `seg_soporte_fno`
            WHERE `tipo` = 1 AND `id_factura_no` IN ($ids)";
    $rs = $cmd->query($sql);
    $soportes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_f = [];
foreach ($facturas_no as $fno) {
    $id_f[] = $fno['id_facturano'];
}
$id_f = implode(',', $id_f);
$detailsfno = [];
if (!empty($id_f)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                `id_detail`, `id_fno`, `codigo`, `detalle`, `val_unitario`, `cantidad`, `p_iva`, `val_iva`, `p_dcto`, `val_dcto`
            FROM
                `ctt_fact_noobligado_det`
            WHERE `id_fno` IN ($id_f)";
        $rs = $cmd->query($sql);
        $detailsfno = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
if (!empty($facturas_no)) {
    foreach ($facturas_no as $fn) {
        $id_fno = $fn['id_facturano'];
        $editar = $borrar = null;
        $key = array_search($id_fno, array_column($soportes, 'id_factura_no'));
        if ($key === false) {
            $enviar = '<button value="' . $id_fno . '" onclick="EnviaDocSoporte2(this)" class="btn btn-outline-info btn-sm btn-circle shadow-gb enviar" title="Reportar Factura"><span class="fas fa-paper-plane fa-lg"></span></button>';
            if (PermisosUsuario($permisos, 5303, 3) || $id_rol == 1) {
                $editar = '<a value="' . $id_fno . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb modificar" title="Modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if (PermisosUsuario($permisos, 5303, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $id_fno . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        } else {
            $enviar = '<a onclick="VerSoporteElectronico2(' . $id_fno . ')" class="btn btn-outline-warning btn-sm btn-circle shadow-gb verSoporte" title="Soporte Documento equivalente"><span class="fab fa-wpforms fa-lg"></span></a>';
        }
        //detalles
        $detalles = '';
        foreach ($detailsfno as $det) {
            if ($det['id_fno'] == $id_fno) {
                $detalles .= '<li>' . $det['detalle'] . '</li>';
            }
        }
        $detalles = $detalles != '' ? '<ul class="mb-0">' . $detalles . '</ul>' : null;
        $data[] = [
            'id_facturano' => $fn['id_facturano'],
            'fec_compra' => $fn['fec_compra'],
            'fec_vence' => $fn['fec_vence'],
            'metodo' => $fn['met_pago'] == '1' ? 'CONTADO' : 'CRÉDITO',
            'forma_pago' => $fn['form_pago'],
            'tipo_doc' => $fn['tipo_documento'],
            'no_doc' => $fn['no_doc'],
            'nombre' => $fn['nombre'],
            'detalles' => $detalles,
            'botones' => '<div class="text-center">' . $editar . $borrar . $enviar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
