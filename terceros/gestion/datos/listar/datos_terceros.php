<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`
                , `seg_terceros`.`id_tercero_api`
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
$id_t = [];
foreach ($terEmpr as $l) {
    $id_t[] = $l['id_tercero_api'];
}
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
$data = [];
if (!empty($terEmpr)) {
    foreach ($terEmpr as $t) {
        $idter = $t['no_doc'];
        $key = array_search($idter, array_column($terceros, 'cc_nit'));
        if ($key !== false) {
            $idT = $terceros[$key]['id_tercero'];
            if (PermisosUsuario($permisos, 5201, 2) || $id_rol == 1) {
                $addresponsabilidad = '<a value="' . $idT . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb responsabilidad" title="+ Responsabilidad Económica"><span class="fas fa-hand-holding-usd fa-lg"></span></a>';
                $addactividad = '<a value="' . $idT . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb actividad" title="+ Actividad Económica"><span class="fas fa-donate fa-lg"></span></a>';
            } else {
                $addresponsabilidad = null;
                $addactividad = null;
            }
            $idTerEmp = $t['id_tercero'];
            $editar = null;
            if (PermisosUsuario($permisos, 5201, 3) || $id_rol == 1) {
                //$editar = '<a value="' . $idter . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                if ($t['estado'] == '1') {
                    $estado = '<button id="btnestado_' . $idTerEmp . '" class="btn-estado" title="Activo"><span class="fas fa-toggle-on fa-lg estado activo" value="' . $idTerEmp . '"></span></button>';
                } else {
                    $estado = '<button id="btnestado_' . $idTerEmp . '"  class="btn-estado" title="Inactivo"><span class="fas fa-toggle-off fa-lg estado inactivo" value="' . $idTerEmp . '"></span></button>';
                }
            } else {
                $estado = $terceros[$key]['estado'];
            }
            if (PermisosUsuario($permisos, 5201, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $idTerEmp . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            } else {
                $borrar = null;
            }
            $detalles = '<a class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" value="' . $terceros[$key]['cc_nit'] . '" title="Detalles"><span class="far fa-eye fa-lg"></span></a>';

            $data[] = [
                'cc_nit' => $terceros[$key]['cc_nit'],
                'nombre_tercero' => mb_strtoupper($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['apellido1']),
                'razon_social' => $terceros[$key]['razon_social'],
                'tipo' => $t['descripcion'],
                'municipio' => $terceros[$key]['nom_municipio'],
                'direccion' => $terceros[$key]['direccion'],
                'telefono' => $terceros[$key]['telefono'],
                'correo' => $terceros[$key]['correo'],
                'estado' => '<div class="text-center">' . $estado . '</div>',
                'botones' => '<div class="text-center">' . $editar . $borrar . $addresponsabilidad . $addactividad . $detalles . '</div>',
            ];
        }
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
