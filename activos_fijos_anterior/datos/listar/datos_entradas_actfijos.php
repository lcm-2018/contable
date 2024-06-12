<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`objeto`
                , `ctt_adquisiciones`.`estado`
                , `ctt_adquisiciones`.`fecha_adquisicion`
                , `seg_terceros`.`id_tercero_api`
                , `tb_tipo_contratacion`.`id_tipo`
            FROM
                `ctt_adquisiciones`
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `tb_tipo_contratacion` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
                INNER JOIN `seg_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            WHERE `vigencia` = '$vigencia' AND `tb_tipo_contratacion`.`id_tipo` = '7'";
    $rs = $cmd->query($sql);
    $ladquis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($ladquis)) {
    foreach ($ladquis as $la) {
        $id_adq = $la['id_tercero_api'] . '|' . $_SESSION['nit_emp'] . '|' . $la['id_adquisicion'];
        $detalles = null;
        if ((intval($permisos['editar']))) {
            $detalles = '<a value="' . $id_adq . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles Recepcionar"><span class="fas fa-eye fa-lg"></span></a>';
            if ($la['estado'] > 10) {
                $detalles = '<a value="' . $id_adq . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb detalles" title="Detalles Recibidos"><span class="fas fa-eye fa-lg"></span></a>';
            }
        }
        if ($la['estado'] >= 9) {
            $data[] = [
                'id_adq' => $la['id_adquisicion'],
                'objeto' => $la['objeto'],
                'fecha' => $la['fecha_adquisicion'],
                'botones' => '<div class="text-center">' . $detalles . '</div>',
            ];
        }
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
