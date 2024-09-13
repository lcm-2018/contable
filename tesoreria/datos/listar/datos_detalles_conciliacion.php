<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
// Div de acciones de la lista
$id_cuenta = isset($_POST['id_cuenta']) ? $_POST['id_cuenta'] : exit('Acceso no disponible');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`fecha`
                , `ctb_fuente`.`cod`
                , `ctb_doc`.`id_manu`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , '--' AS `documento`
                , `ctb_libaux`.`id_ctb_libaux`
                , `tes_conciliacion_detalle`.`id_ctb_libaux` AS `conciliado`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tes_conciliacion_detalle`
                    ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)   
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id_cuenta AND `ctb_doc`.`estado` = 2)";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
$terceros = [];
if (!empty($lista)) {
    foreach ($lista as $lp) {
        if ($lp['id_tercero_api'] != '') {
            $id_t[] = $lp['id_tercero_api'];
        }
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
    $cmd = null;
    foreach ($lista as $lp) {
        $chk = $lp['conciliado'] > 0 ? 'checked' : '';
        $check = '<input ' . $chk . ' type="checkbox" name="check[]" onclick="GuardaDetalleConciliacion(this)" text="' . $lp['id_ctb_libaux'] . '">';
        $key = array_search($lp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        $nombre = $key !== false ? ltrim($terceros[$key]['nom_tercero'] . ' -> ' . $terceros[$key]['nit_tercero']) : '---';
        $estado = $lp['conciliado'] > 0 ? 'Conciliado' : 'Pendiente';
        $data[] = [

            'fecha' => date('Y-m-d', strtotime($lp['fecha'])),
            'no_comprobante' => $lp['cod'] . $lp['id_manu'],
            'tercero' => $nombre,
            'documento' => $lp['documento'],
            'debito' => '<div class="text-right">' . pesos($lp['debito']) . '</div>',
            'credito' => '<div class="text-right">' . pesos($lp['credito']) . '</div>',
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'accion' => '<div class="text-center vertical-align-middle">' . $check . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);

function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
