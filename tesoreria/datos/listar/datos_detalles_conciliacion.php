<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
// Div de acciones de la lista
$id_cuenta = isset($_POST['id_cuenta']) ? $_POST['id_cuenta'] : exit('Acceso no disponible');
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_mes`,`codigo`,`nom_mes`,`fin_mes`
            FROM `nom_meses`
            WHERE `codigo` = '$mes'";
    $rs = $cmd->query($sql);
    $df = $rs->fetch(PDO::FETCH_ASSOC);
    $dia = $mes == '02' ? cal_days_in_month(CAL_GREGORIAN, 2, $vigencia) : $df['fin_mes'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = $vigencia . '-' . $mes . '-' . $dia;
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
                , `tes_conciliacion_detalle`.`fecha_marca` AS `marca`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp`  ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tes_cuentas`  ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc`   ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tes_conciliacion_detalle` ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
                LEFT JOIN `tes_conciliacion` ON (`tes_conciliacion`.`id_conciliacion` = `tes_conciliacion_detalle`.`id_concilia`)   
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id_cuenta AND `ctb_doc`.`estado` = 2 
                    AND `ctb_doc`.`fecha` <= '$fecha' AND (`tes_conciliacion_detalle`.`fecha_marca` >= '$fecha' or `tes_conciliacion_detalle`.`fecha_marca` IS NULL)
                    AND (`tes_conciliacion`.`vigencia` = '$vigencia' OR `tes_conciliacion`.`vigencia` IS NULL))";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
$terceros = [];
$tot_deb = 0;
$tot_cre = 0;
$tdc = 0;
$tcc = 0;
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
        if ($lp['conciliado'] > 0  && $lp['marca'] <= $fecha) {
            $chk =  'checked';
            $estado = 'Conciliado';
        } else {
            $chk =  '';
            $estado = 'Pendiente';
        }
        if ($lp['conciliado'] > 0  && $lp['marca'] > $fecha) {
            $chk =  'disabled';
            $estado = 'Marcado';
        }

        $tot_deb += $lp['debito'];
        $tot_cre += $lp['credito'];
        if ($lp['conciliado'] > 0) {
            $tdc += $lp['debito'];
            $tcc += $lp['credito'];
        }
        $check = '<input ' . $chk . ' type="checkbox" name="check[]" onclick="GuardaDetalleConciliacion(this)" text="' . $lp['id_ctb_libaux'] . '">';
        $key = array_search($lp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        $nombre = $key !== false ? ltrim($terceros[$key]['nom_tercero'] . ' -> ' . $terceros[$key]['nit_tercero']) : '---';
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
    $tot_deb = $tot_deb - $tdc;
    $tot_cre = $tot_cre - $tcc;
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'tot_deb' => $tot_deb,
    'tot_cre' => $tot_cre
];


echo json_encode($datos);

function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
