<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../../../conexion.php';
include '../../../../permisos.php';
include '../../../../terceros.php';
$vigencia = $_SESSION['vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id`, `descripcion` FROM `ctt_estado_adq`";
    $rs = $cmd->query($sql);
    $estado_adq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_area` FROM `tb_area_responsable` WHERE `id_user` = $iduser GROUP BY `id_area`";
    $rs = $cmd->query($sql);
    $areas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if ($id_rol == '1') {
    $usuario = '';
} else {
    if (!empty($areas)) {
        $areas = array_column($areas, 'id_area');
        $areas = implode(',', $areas);
        $usuario = " AND `ctt_adquisiciones`.`id_area` IN ($areas)";
    } else {
        $usuario = " AND `ctt_adquisiciones`.`id_user_reg` =" . $iduser;
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `modalidad`
                , `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`val_contrato`
                , `ctt_adquisiciones`.`estado`
                , `ctt_adquisiciones`.`fecha_adquisicion`
                , `ctt_adquisiciones`.`objeto`
                , `tb_terceros`.`id_tercero_api`
                , `tb_terceros`.`nom_tercero`
                , `pto_cdp`.`id_pto_cdp`
                , `pto_cdp`.`estado` AS `status`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `ctt_modalidad` 
                ON (`ctt_adquisiciones`.`id_modalidad` = `ctt_modalidad`.`id_modalidad`)
            LEFT JOIN `tb_terceros`
                ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            LEFT JOIN `pto_cdp`
                ON (`pto_cdp`.`id_pto_cdp` = `ctt_adquisiciones`.`id_cdp`)
            WHERE `vigencia` = '$vigencia'" . $usuario;
    $rs = $cmd->query($sql);
    $ladquis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($ladquis)) {
    foreach ($ladquis as $la) {
        $id_adq = $la['id_adquisicion'];
        $editar = null;
        $detalles = null;
        $anular = null;
        $duplicar = null;
        if ($la['estado'] <= '6' && (PermisosUsuario($permisos, 5302, 3) || $id_rol == 1) && ($la['status'] == '0' || $la['id_pto_cdp'] == '')) {
            $anular = '<a value="' . $id_adq . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb anular" title="Anular"><span class="fas fa-ban fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5302, 3) || $id_rol == 1) {
            $detalles = '<a value="' . $id_adq . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            if ($la['estado'] <= 2) {
                $editar = '<a value="' . $id_adq . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
        }
        if ($la['estado'] >= '6') {
            $duplicar = '<a value="' . $id_adq . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb duplicar" title="Duplicar"><span class="fas fa-clone fa-lg"></span></a>';
        }
        $accion = null;
        switch ($la['estado']) {
            case 0:
                $accion = '<a class="btn btn-outline-secondary btn-sm btn-circle shadow-gb disabled" title="Orden sin productos"><span class="fas fa-sign-out-alt fa-lg"></span></a>';
                break;
                /*
            case 1:
                $accion = '<a class="btn btn-outline-secondary btn-sm btn-circle shadow-gb disabled" title="Orden sin productos"><span class="fas fa-sign-out-alt fa-lg"></span></a>';
                break;
            case 2:
                $accion = '<a value="' . $id_adq . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb enviar" title="Enviar cotización"><span class="fas fa-sign-out-alt fa-lg"></span></a>';
                break;
            case 3:
                $accion = '<a value="' . $id_adq . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb bajar" title="Bajar cotización"><span class="fas fa-chevron-circle-down fa-lg"></span></a>';
                break;
            case 4:
                $accion = '<a value="' . $id_adq . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb comprobar" title="Ver cotización de terceros"><span class="fas fa-clipboard-check fa-lg"></span></a>';
                break;
            case 7:
                $accion = '<a value="' . $id_adq . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb envContrato" title="Enviar Contrato"><span class="fas fa-file-upload fa-lg"></span></a>';
                break;
            */
        }
        if ((PermisosUsuario($permisos, 5302, 4) || $id_rol == 1) && $la['estado'] <= 2) {
            $borrar = '<a value="' . $id_adq . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        if ($la['estado'] == '99') {
            $borrar = null;
            $editar = null;
            $detalles = '<span class="badge badge-secondary">ANULADO</span>';
            $accion = null;
            $anular = null;
        }
        $est = $la['estado'];
        $tercer = $la['nom_tercero'] ? $la['nom_tercero'] : '---';
        $key = array_search($est, array_column($estado_adq, 'id'));
        $estd = $estado_adq[$key]['descripcion'];
        $data[] = [
            'id' => $id_adq,
            'modalidad' => $la['modalidad'],
            'adquisicion' => 'ADQ-' . $id_adq,
            'valor' => '<div class="text-right">' . pesos($la['val_contrato']) . '</div>',
            'fecha' => $la['fecha_adquisicion'],
            'objeto' => $la['objeto'],
            'tercero' => $tercer,
            'estado' => $estd,
            'botones' => '<div class="text-center">' . $editar . $borrar . $detalles . $accion . $anular . $duplicar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
