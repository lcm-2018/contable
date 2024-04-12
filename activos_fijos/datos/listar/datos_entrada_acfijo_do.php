<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$tipo = isset($_POST['tip_eaf']) ? $_POST['tip_eaf'] : exit('Accion no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `acf_entrada`.`id_entra_af`
                , `acf_entrada`.`id_tercero_api`
                , `acf_entrada`.`id_tipo_entrada`
                , `acf_tipo_entrada`.`descripcion`
                , `acf_entrada`.`acta_remision`
                , `acf_entrada`.`fec_acta_remision`
                , `acf_entrada`.`observacion`
                , `acf_entrada`.`estado`
            FROM
                `acf_entrada`
                INNER JOIN `acf_tipo_entrada` 
                    ON (`acf_entrada`.`id_tipo_entrada` = `acf_tipo_entrada`.`id_entrada`)
            WHERE `acf_entrada`.`vigencia` = '$vigencia' AND `acf_entrada`.`id_tipo_entrada` = '$tipo'";
    $rs = $cmd->query($sql);
    $lEntAF = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter = '0';
foreach ($lEntAF as $laf) {
    $id_ter .= ',' . $laf['id_tercero_api'];
}
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$data = [];
if (!empty($lEntAF)) {
    foreach ($lEntAF as $laf) {
        $id_eaf = $laf['id_entra_af'];
        $key = array_search($laf['id_tercero_api'], array_column($dat_ter, 'id_tercero'));
        if (false !== $key) {
            $tercer = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['razon_social'];
        } else {
            $tercer = '';
        }
        $detalles = $editar = $borrar = null;
        if ($laf['estado'] == 1) {
            if (PermisosUsuario($permisos, 5701, 3) || $id_rol == 1) {
                $editar = '<a value="' . $id_eaf . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if (PermisosUsuario($permisos, 5701, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $id_eaf . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        switch ($laf['estado']) {
            case 1:
                $estado = 'INICIALIZADO';
                $coloricon = 'warning';
                break;
            case 2:
                $estado = 'ABIERTO';
                $coloricon = 'secondary';
                break;
            case 3:
                $estado = 'CERRADO';
                $coloricon = 'info';
                break;
        }
        if (PermisosUsuario($permisos, 5701, 1) || $id_rol == 1) {
            $detalles = '<a value="' . $id_eaf . '" class="btn btn-outline-' . $coloricon . ' btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
        }
        $data[] = [
            "id_entrada" => $id_eaf,
            "tercero" => $tercer,
            "acta_remi" => $laf['acta_remision'],
            "fecha" => $laf['fec_acta_remision'],
            "observa" => $laf['observacion'],
            "estado" => $estado,
            "botones" => '<div class="text-center">' . $editar . $borrar . $detalles . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
