<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
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
                `seg_fact_noobligado`.`id_facturano`
                , `tb_tipos_documento`.`descripcion` AS `tipo_documento`
                , `seg_terceros_noblig`.`no_doc`
                , `seg_terceros_noblig`.`nombre`
                , `seg_fact_noobligado`.`fec_compra`
                , `seg_fact_noobligado`.`fec_vence`
                , `seg_fact_noobligado`.`met_pago`
                , `seg_fact_noobligado`.`forma_pago`
                , `nom_metodo_pago`.`metodo` AS `form_pago`
                , `seg_fact_noobligado`.`vigencia`
                , `seg_fact_noobligado`.`estado`
            FROM
                `seg_fact_noobligado`
                INNER JOIN `seg_terceros_noblig` 
                    ON (`seg_fact_noobligado`.`id_tercero_no` = `seg_terceros_noblig`.`id_tercero`)
                INNER JOIN `nom_metodo_pago` 
                    ON (`seg_fact_noobligado`.`forma_pago` = `nom_metodo_pago`.`id_metodo_pago`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`seg_terceros_noblig`.`id_tdoc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE `seg_fact_noobligado`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $facturas_no = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_f = '0';
foreach ($facturas_no as $factura_no) {
    $id_f .= ',' . $factura_no['id_facturano'];
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_detail`, `id_fno`, `codigo`, `detalle`, `val_unitario`, `cantidad`, `p_iva`, `val_iva`, `p_dcto`, `val_dcto`
            FROM
                `seg_fact_noobligado_det`
            WHERE `id_fno` IN ($id_f)";
    $rs = $cmd->query($sql);
    $detailsfno = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($facturas_no)) {
    foreach ($facturas_no as $fn) {
        $id_fno = $fn['id_facturano'];
        $editar = $borrar = null;
        if ($fn['estado'] == '1') {
            $enviar = '<a value="' . $id_fno . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb enviar" title="Reportar Factura"><span class="fas fa-paper-plane fa-lg"></span></a>';
            if ((intval($permisos['editar'])) == 1) {
                $editar = '<a value="' . $id_fno . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb modificar" title="Modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if ((intval($permisos['borrar'])) == 1) {
                $borrar = '<a value="' . $id_fno . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        } else {
            $enviar = '<a value="' . $id_fno . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb verSoporte" title="Soporte Documento equivalente"><span class="fab fa-wpforms fa-lg"></span></a>';
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
