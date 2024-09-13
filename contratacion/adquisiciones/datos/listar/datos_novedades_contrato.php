<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_ct = isset($_POST['id_csp']) ? $_POST['id_csp'] : exit('Acción no permitida');
$id_ct = $id_ct == '' ? 0 : $id_ct;
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                *
            FROM 
                (SELECT 
                    `id_nov_con`,`id_tip_nov`,`id_adq`,`val_adicion` AS `valor1`, '0' AS `valor2`,`fec_adcion` AS `fecha`,`fec_ini_prorroga`AS `inicia`,`fec_fin_prorroga` AS `fin`,`observacion` 
                FROM 
                    `ctt_novedad_adicion_prorroga`
                WHERE `id_adq` = $id_ct
                UNION ALL
                SELECT 
                    `id_cesion`,`id_tipo_nov`,`id_adq`, '0' AS `valor1`, '0' AS `valor2`, `fec_cesion`, '' AS `inicia`, '' AS `fin`, `observacion`
                FROM 
                    `ctt_novedad_cesion`
                WHERE `id_adq` = $id_ct
                UNION ALL
                SELECT 
                    `id_liquidacion`, `id_tipo_nov`, `id_adq`, `val_cte`, `val_cta`, `fec_liq`, '' AS `inicia`, '' AS `fin`, `observacion`
                FROM 
                    `ctt_novedad_liquidacion`
                WHERE `id_adq` = $id_ct
                UNION ALL
                SELECT
                    `ctt_novedad_reinicio`.`id_reinicio`
                    , `ctt_novedad_reinicio`.`id_tipo_nov`
                    , `ctt_novedad_reinicio`.`id_suspension`
                    , '0' AS `valor1`, '0' AS `valor2`
                    , `ctt_novedad_reinicio`.`fec_reinicia`
                    , '' AS `inicia`, '' AS `fin`
                    , `ctt_novedad_reinicio`.`observacion`
                FROM 
                    `ctt_novedad_reinicio`
                INNER JOIN `ctt_novedad_suspension`
                    ON (`ctt_novedad_reinicio`.`id_suspension` = `ctt_novedad_suspension`.`id_suspension`)
                WHERE `ctt_novedad_suspension`.`id_adq` = $id_ct
                UNION ALL
                SELECT
                    `id_suspension`, `id_tipo_nov`, `id_adq`,'0' AS `valor1`, '0' AS `valor2`, `fec_inicia`, `fec_inicia` AS `inicia`, `fec_fin`, `observacion`  
                FROM
                    `ctt_novedad_suspension`
                WHERE `id_adq` = $id_ct
                UNION ALL
                SELECT
                    `id_terminacion`,`id_tipo_nov`,`id_adq`,'0' AS `valor1`, '0' AS `valor2`,'' AS `fec_liq`, '' AS `inicia`, '' AS `fin`, `observacion`
                FROM
                    `ctt_novedad_terminacion`
                WHERE `id_adq` = $id_ct) AS `t` 
            INNER JOIN `ctt_tipo_novedad`
                ON(`t`.`id_tip_nov` = `ctt_tipo_novedad`.`id_novedad`)";
    $rs = $cmd->query($sql);
    $novedades = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($novedades)) {
    foreach ($novedades as $nv) {
        $id_nov = $nv['id_nov_con'] . '|' . $nv['id_tip_nov'];
        if (PermisosUsuario($permisos, 5302, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_nov . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5302, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_nov . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            't_novedad' => $nv['descripcion'],
            'fecha' => $nv['fecha'],
            'valor1' => '<div class="text-right">' . pesos($nv['valor1']) . '</div>',
            'valor2' => '<div class="text-right">' . pesos($nv['valor2']) . '</div>',
            'inicia' => $nv['inicia'],
            'fin' => $nv['fin'],
            'observacion' => $nv['observacion'],
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

        ];
    }
}
$datos = ['data' => $data];

echo json_encode($datos);