<?php

use Sabberworm\CSS\Value\PrimitiveValue;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_adquisicion`, `estado` FROM `ctt_adquisiciones` WHERE `estado` = '3' AND `vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $estado_cot = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
$id_user = $_SESSION['id_user'];
if (isset($estado_cot)) {
    $nit = $_SESSION['nit_emp'];
    $estado = 4;
    $ids = [];
    foreach ($estado_cot as $ec) {
        $ids[] = $ec['id_adquisicion'];
    }
    $payload = json_encode(['ids' => $ids, 'nit' => $nit]);
    $url = $api . 'terceros/datos/res/listar/estado_cotizacion';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $res =  json_decode($result, true);
    foreach ($estado_cot as $ec) {
        $id_adqui = $ec['id_adquisicion'];
        $verf = 0;
        $key = array_search($id_adqui, array_column($res, 'id_adq'));
        if ($key !== false) {
            $verf = $res[$key]['estado'];
        }
        if ($verf == 2) {
            try {
                $date = new DateTime('now', new DateTimeZone('America/Bogota'));
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $estado, PDO::PARAM_INT);
                $sql->bindParam(2, $id_user, PDO::PARAM_INT);
                $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(4, $id_adqui, PDO::PARAM_INT);
                $sql->execute();
                if (!($sql->rowCount() > 0)) {
                    echo $sql->errorInfo()[2];
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        } else {
        }
    }
}
if ($_SESSION['id_user'] == 1) {
    $usuario = '';
} else {
    $usuario = " AND ctt_adquisiciones.id_user_reg =" . $id_user;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT modalidad, id_adquisicion, val_contrato, ctt_adquisiciones.estado, fecha_adquisicion, objeto, id_tercero_api
            FROM
                ctt_adquisiciones
            INNER JOIN ctt_modalidad 
                ON (ctt_adquisiciones.id_modalidad = ctt_modalidad.id_modalidad)
            LEFT JOIN seg_terceros
                ON (ctt_adquisiciones.id_tercero = seg_terceros.id_tercero)
            WHERE vigencia = '$vigencia'" . $usuario;
    $rs = $cmd->query($sql);
    $ladquis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($ladquis as $l) {
    if ($l['id_tercero_api'] != '') {
        $id_t[] = $l['id_tercero_api'];
    }
}
$terceros = [];
$payload = json_encode($id_t);
if (!empty($id_t)) {
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros =  json_decode($result, true);
}
if ($terceros == '0' || $terceros == '') {
    $terceros = [];
}
if (!empty($ladquis)) {
    foreach ($ladquis as $la) {
        $id_adq = $la['id_adquisicion'];
        $editar = null;
        $detalles = null;
        $anular = null;
        $duplicar = null;
        if ($la['estado'] <= '5' && (PermisosUsuario($permisos, 5302, 3) || $id_rol == 1)) {
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
        $key = array_search($est, array_column($estado_adq, 'id'));
        $keyt = array_search($la['id_tercero_api'], array_column($terceros, 'id_tercero'));
        if ($keyt === false) {
            $tercer = '';
        } else {
            $tercer = $terceros[$keyt]['apellido1'] . ' ' . $terceros[$keyt]['apellido2'] . ' ' .  $terceros[$keyt]['nombre1'] . ' ' .  $terceros[$keyt]['nombre2'] . ' ' . $terceros[$keyt]['razon_social'];
        }
        $data[] = [
            'id' => $id_adq,
            'modalidad' => $la['modalidad'],
            'adquisicion' => 'ADQ-' . $id_adq,
            'valor' => '<div class="text-right">' . pesos($la['val_contrato']) . '</div>',
            'fecha' => $la['fecha_adquisicion'],
            'objeto' => $la['objeto'],
            'tercero' => $tercer,
            'estado' => $estado_adq[$key]['descripcion'],
            'botones' => '<div class="text-center">' . $editar . $borrar . $detalles . $accion . $anular . $duplicar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
