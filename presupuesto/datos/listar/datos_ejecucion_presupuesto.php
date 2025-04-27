<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);
// Div de acciones de la lista
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search'] ?? '';
$anulados = $_POST['anulados'] ?? 0;
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (pto_cdp.id_manu LIKE '%$search_value%' OR pto_cdp.objeto LIKE '%$search_value%' OR pto_cdp.fecha LIKE '%$search_value%')";
} else {
    $buscar = '';
}
if ($anulados == 1 || !empty($search_value)) {
    $buscar .= " AND pto_cdp.estado >= 0";
} else {
    $buscar .= " AND pto_cdp.estado > 0";
}
try {
    $sql = "SELECT
                `pto_cdp`.`id_pto_cdp`
                , `pto_cdp`.`id_manu`
                , `pto_cdp`.`fecha`
                , `pto_cdp`.`objeto`
                , `pto_cdp`.`estado`
                , IFNULL(`cdp`.`val_cdp`,0) AS `val_cdp`
                , IFNULL(`cdp`.`val_lib_cdp`,0) AS `val_lib_cdp`
                , IFNULL(`crp`.`val_crp`,0) AS `val_crp`
                , IFNULL(`crp`.`val_lib_crp`,0) AS `val_lib_crp`
            FROM `pto_cdp`
            LEFT JOIN 
                (SELECT
                    `id_pto_cdp`
                    , SUM(`valor`) AS `val_cdp`
                    , SUM(`valor_liberado`) AS `val_lib_cdp`
                FROM
                    `pto_cdp_detalle`
                GROUP BY `id_pto_cdp`) AS `cdp`
                ON (`pto_cdp`.`id_pto_cdp` = `cdp`.`id_pto_cdp`)
            LEFT JOIN
                (SELECT
                    `pto_cdp_detalle`.`id_pto_cdp`
                    , SUM(`pto_crp_detalle`.`valor`) AS `val_crp`
                    , SUM(`pto_crp_detalle`.`valor_liberado`) AS `val_lib_crp`
                FROM
                    `pto_crp_detalle`
                    INNER JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                    INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                WHERE (`pto_crp`.`estado` > 0)
                GROUP BY `pto_cdp_detalle`.`id_pto_cdp`) AS `crp`
                ON (`pto_cdp`.`id_pto_cdp` = `crp`.`id_pto_cdp`)
            WHERE `pto_cdp`.`id_pto` = $id_pto_presupuestos $buscar
            ORDER BY `pto_cdp`.`id_manu` DESC
            LIMIT $start, $length";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `pto_cdp`.`id_pto_cdp`
                , `pto_crp`.`id_pto_crp`
            FROM
                `pto_cdp`
                LEFT JOIN `pto_crp` 
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`pto_cdp`.`id_pto` = $id_pto_presupuestos)";
    $rs = $cmd->query($sql);
    $registros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// obtener el numero total de registros de la anterior consulta
try {
    $sql = "SELECT COUNT(*) AS `total` FROM `pto_cdp` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $anular = $dato = $borrar = $imprimir = $historial = $abrir = null;
        $id_pto = $lp['id_pto_cdp'];
        // Sumar el valor del cdp de la tabla id_pto_mtvo
        $valor_cdp = number_format($lp['val_cdp'], 2, ',', '.');
        $valor_cdp_lib = number_format($lp['val_lib_cdp'], 2, ',', '.');
        $valor_crp = number_format($lp['val_crp'], 2, ',', '.');
        $valor_crp_lib = number_format($lp['val_lib_crp'], 2, ',', '.');
        $val_cdp = $lp['val_cdp'] - $lp['val_lib_cdp'];
        $val_crp = $lp['val_crp'] - $lp['val_lib_crp'];
        $cxregistrar = $val_cdp - $val_crp;
        $xregistrar = number_format($cxregistrar, 2, ',', '.');
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        $info = base64_encode($id_pto . '|cdp');
        if (!($fecha <= $fecha_cierre) && (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1)) {
            $anular = '<button text="' . $info . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Anular" onclick="anulacionPto(this);"><span class="fas fa-ban fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            if ($lp['estado'] == 2) {
                $registrar = '<a value="' . $id_pto . '" onclick="CargarFormularioCrpp(' . $id_pto . ')" class="text-blue " role="button" title="Detalles"><span class="badge badge-pill badge-primary">Registrar</span></a>';
            } else {
                $mje = "Primero debe cerrar el CDP";
                $registrar = '<a onclick="mjeError(\'' . htmlspecialchars($mje, ENT_QUOTES) . '\')" class="text-blue" role="button" title="Detalles"><span class="badge badge-pill badge-secondary">Registrar</span></a>';
            }
            if ($cxregistrar  == 0) {
                $registrar = '--';
            }
            if ($fecha <= $fecha_cierre || $val_crp > 0) {
                $anular = null;
            }
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $historial = '<a value="' . $id_pto . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="Ver Historial" onclick="verLiquidarCdp(' . $id_pto . ');"><span class="fas fa-history fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) {
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoCdp(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb" title="Impirmir"><span class="fas fa-print fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_pto . '"    onclick="eliminarCdp(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb " title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($fecha < $fecha_cierre) {
                $borrar = null;
            }
            if ($lp['val_cdp'] ==  $cxregistrar) {
            } else {
                $borrar = null;
                $editar = null;
            }
        }
        $key = array_search($id_pto, array_column($registros, 'id_pto_cdp'));
        $valida = $registros[$key]['id_pto_crp'] == '' ? true : false;
        if (($id_rol == 1 || PermisosUsuario($permisos, 5401, 5)) && $valida) {
            if ($lp['estado'] == 2) {
                $abrir = '<a onclick="abrirCdp(' . $id_pto . ')" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb " title="Abrir CDP"><span class="fas fa-lock fa-lg"></span></a>';
            } else {
                $abrir = '<a onclick="cerrarCdp(' . $id_pto . ')" class="btn btn-outline-info btn-sm btn-circle shadow-gb " title="Cerrar CDP"><span class="fas fa-unlock fa-lg"></span></a>';
            }
            if ($fecha < $fecha_cierre) {
                $abrir = null;
            }
        }
        if ($lp['estado'] == 0) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $anular = null;
            $historial = null;
            $abrir = null;
            $dato = '<span class="badge badge-pill badge-secondary">Anulado</span>';
            $registrar = '';
            $xregistrar = '';
        }
        if ($lp['estado'] >= 2) {
            $borrar = null;
            $editar = null;
        }
        $historial = null;
        $data[] = [
            'numero' => $lp['id_manu'],
            'fecha' => $fecha,
            'objeto' => $lp['objeto'],
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'liberado' =>  '<div class="text-right">' . $valor_cdp_lib . '</div>',
            'xregistrar' =>  '<div class="text-right">' . $xregistrar  . '</div>',
            'accion' => '<div class="text-center">' . $registrar . '</div>',
            'botones' => '<div class="text-center">' . $editar . $detalles . $imprimir . $anular . $borrar . $dato . $historial . $abrir . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
];


echo json_encode($datos);
