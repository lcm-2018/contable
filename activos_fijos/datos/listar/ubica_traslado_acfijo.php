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
$id_ser = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Accion no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_ubica_traslado_centro_costo`.`id_traslado`
                , `tb_centros_costo`.`descripcion` AS `centro_costo`
                , `tb_sedes`.`nom_sede` AS `sede`
                , `seg_ubica_traslado_centro_costo`.`fecha`
                , `nom_estado_acfijo`.`descripcion` AS `estado`
                , `seg_ubica_traslado_centro_costo`.`id_tercero_api`
                , `seg_ubica_traslado_centro_costo`.`observaciones`
            FROM
                `seg_ubica_traslado_centro_costo`
                INNER JOIN `tb_centro_costo_x_sede` 
                    ON (`seg_ubica_traslado_centro_costo`.`id_centro_costo` = `tb_centro_costo_x_sede`.`id_x_sede`)
                INNER JOIN `tb_centros_costo` 
                    ON (`tb_centro_costo_x_sede`.`id_centro_c` = `tb_centros_costo`.`id_centro`)
                INNER JOIN `tb_sedes` 
                    ON (`tb_centro_costo_x_sede`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `nom_estado_acfijo` 
                    ON (`seg_ubica_traslado_centro_costo`.`estado` = `nom_estado_acfijo`.`id_estado`)
            WHERE `seg_ubica_traslado_centro_costo`.`id_serial` = '$id_ser'";
    $rs = $cmd->query($sql);
    $ubicacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`
                , `seg_terceros`.`tipo_doc`
                , `seg_terceros`.`no_doc`
                , `seg_terceros`.`estado`
                , `tb_tipo_tercero`.`descripcion`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
                INNER JOIN `tb_tipo_tercero` 
                    ON (`tb_rel_tercero`.`id_tipo_tercero` = `tb_tipo_tercero`.`id_tipo`)";
    $rs = $cmd->query($sql);
    $terEmpr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ced = '0';
foreach ($terEmpr as $tE) {
    $ced .= ',' . $tE['no_doc'];
}
//API URL
$url = $api . 'terceros/datos/res/lista/' . $ced;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
$data = [];
echo json_encode($data);
exit();
if (!empty($ubicacion)) {
    foreach ($ubicacion as $u) {
        $id_ut = $u['id_traslado'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_ut . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_ut . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar activo fijo"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $key = array_search($u['id_tercero_api'], array_column($terceros, 'id_tercero'));
        if ($key !== false) {
            $nom_tercero = mb_strtoupper($terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . $terceros[$key]['razon_social'] . ' || ' . $terceros[$key]['cc_nit']);
        } else {
            $nom_tercero = '';
        }
        $data[] = [
            "id_traslado" => $id_ut,
            "centro_costo" => $u['centro_costo'],
            "sede" => $u['sede'],
            "fecha" => $u['fecha'],
            "estado" => $u['estado'],
            "resposable" => $nom_tercero,
            "observaciones" => $u['observaciones'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
