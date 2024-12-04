<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_vacaciones`.`id_vac`
                , `nom_empleado`.`no_documento`
                , CONCAT_WS(' ',`nom_empleado`.`apellido1`, `nom_empleado`.`apellido2`, `nom_empleado`.`nombre1`, `nom_empleado`.`nombre2`) AS `nombre`
                , `nom_liq_vac`.`fec_inicio`
                , `nom_liq_vac`.`fec_fin`
                , `nom_liq_vac`.`dias_liqs`
                , `nom_liq_vac`.`val_liq`
                , `nom_liq_vac`.`val_prima_vac`
                , `nom_liq_vac`.`val_bsp`
                , `nom_liq_vac`.`val_bon_recrea`
                , `nom_vacaciones`.`corte`
                , `nom_vacaciones`.`anticipo`
                , `nom_vacaciones`.`dias_habiles`
            FROM
                `nom_liq_vac`
                INNER JOIN `nom_vacaciones` 
                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                INNER JOIN `nom_empleado` 
                    ON (`nom_vacaciones`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE `nom_liq_vac`.`anio_vac` = '$vigencia'";
    $rs = $cmd->query($sql);
    $vac_liquidadas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($vac_liquidadas)) {
    foreach ($vac_liquidadas as $vl) {
        $anticipo = $vl['anticipo'] == 1 ? '<span class="badge badge-success">SI</span>' : '<span class="badge badge-secondary">NO</span>';
        $data[] = [
            'id' => $vl['id_vac'],
            'no_doc' => $vl['no_documento'],
            'nombre' => mb_strtoupper($vl['nombre']),
            'fec_inicia' => $vl['fec_inicio'],
            'fec_fin' => $vl['fec_fin'],
            'dias_liq' => $vl['dias_liqs'],
            'val_vac' => '<div class="text-right">' . pesos($vl['val_liq']) . '</div>',
            'val_pri_vac' => '<div class="text-right">' . pesos($vl['val_prima_vac']) . '</div>',
            'val_bsp' => '<div class="text-right">' . pesos($vl['val_bsp']) . '</div>',
            'val_brecrea' => '<div class="text-right">' . pesos($vl['val_bon_recrea']) . '</div>',
            'corte' => $vl['corte'],
            'anticipo' => '<div class="text-center">' . $anticipo . '</div>',
            'dias_hab' => $vl['dias_habiles'],
            'total' => '<div class="text-right">' . pesos($vl['val_liq'] + $vl['val_prima_vac'] + $vl['val_bsp'] + $vl['val_bon_recrea']) . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];
echo json_encode($datos);
