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
    $buscar = "AND (pto_rad.id_manu LIKE '%$search_value%' OR pto_rad.objeto LIKE '%$search_value%' OR pto_rad.fecha LIKE '%$search_value%' OR pto_rad.num_factura LIKE '%$search_value%')";
} else {
    $buscar = ' ';
}
if ($anulados == 1 || !empty($search_value)) {
    $buscar .= " AND pto_rad.estado >= 0";
} else {
    $buscar .= " AND pto_rad.estado >= 0";
}

//----------- filtros--------------------------

$andwhere = " ";

if (isset($_POST['id_manu']) && $_POST['id_manu']) {
    $andwhere .= " AND pto_rad.id_manu LIKE '%" . $_POST['id_manu'] . "%'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $andwhere .= " AND pto_rad.fecha BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['objeto']) && $_POST['objeto']) {
    $andwhere .= " AND pto_rad.objeto LIKE '%" . $_POST['objeto'] . "%'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    if ($_POST['estado'] == "-1") {
        $andwhere .= " AND pto_rad.estado>=" . $_POST['estado'];
    } else {
        $andwhere .= " AND pto_rad.estado=" . $_POST['estado'];
    }
}

try {
    $sql = "SELECT
                `pto_rad`.`id_pto_rad`
                , `pto_rad`.`id_manu`
                , `pto_rad`.`fecha`
                , `pto_rad`.`objeto`
                , `pto_rad`.`estado`
                , `pto_rad`.`num_factura`
                , IFNULL(`rad`.`valor`,0) AS `val_cdp`
                , IFNULL(`rad`.`liberado`,0) AS `val_lib_cdp`
                , `tb_terceros`.`nom_tercero` AS `tercero`
            FROM `pto_rad`
            LEFT JOIN 
                (SELECT
                    `id_pto_rad`
                    , SUM(`valor`) AS `valor`
                    , SUM(`valor_liberado`) AS `liberado`
                FROM
                    `pto_rad_detalle`
                GROUP BY `id_pto_rad`) AS `rad`
                ON (`pto_rad`.`id_pto_rad` = `rad`.`id_pto_rad`)
            LEFT JOIN `tb_terceros`
                ON(`pto_rad`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE `pto_rad`.`id_pto` = $id_pto_presupuestos $buscar $andwhere
            ORDER BY `pto_rad`.`id_manu` DESC
            LIMIT $start, $length";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// obtener el numero total de registros de la anterior consulta
try {
    $sql = "SELECT COUNT(*) AS `total` FROM `pto_rad` WHERE `id_pto` = $id_pto_presupuestos $buscar $andwhere";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFiltered = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT COUNT(*) AS `total` FROM `pto_rad` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $anular = $dato = $borrar = $imprimir = $abrir = null;
        $id_pto = $lp['id_pto_rad'];
        // Sumar el valor del cdp de la tabla id_pto_mtvo
        $valor_cdp = number_format($lp['val_cdp'], 2, ',', '.');
        $valor_cdp_lib = number_format($lp['val_lib_cdp'], 2, ',', '.');
        $val_cdp = $lp['val_cdp'] - $lp['val_lib_cdp'];
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        $info = base64_encode($id_pto);
        if (!($fecha <= $fecha_cierre) && (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1)) {
            $anular = '<button text="' . $info . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Anular" onclick="anulacionPtoRad(this);"><span class="fas fa-ban fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            if ($fecha <= $fecha_cierre) {
                $anular = null;
            }
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) {
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoRad(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb" title="Impirmir"><span class="fas fa-print fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_pto . '"    onclick="eliminarRad(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb " title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($fecha <= $fecha_cierre) {
                $borrar = null;
            }
        }

        if (($id_rol == 1 || PermisosUsuario($permisos, 5401, 5))) {
            if ($lp['estado'] == 2) {
                $abrir = '<a onclick="abrirRad(' . $id_pto . ')" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb " title="Abrir"><span class="fas fa-lock fa-lg"></span></a>';
            } else {
                $abrir = '<a onclick="cerrarRad(' . $id_pto . ')" class="btn btn-outline-info btn-sm btn-circle shadow-gb " title="Cerrar"><span class="fas fa-unlock fa-lg"></span></a>';
            }
        }
        if ($fecha <= $fecha_cierre) {
            $abrir = null;
        }
        if ($lp['estado'] == 0) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $anular = null;
            $abrir = null;
            $dato = '<span class="badge badge-pill badge-secondary">Anulado</span>';
        }
        if ($lp['estado'] >= 2) {
            $borrar = null;
            $editar = null;
        }
        $data[] = [
            'numero' => $lp['id_manu'],
            'factura' => $lp['num_factura'],
            'fecha' => $fecha,
            'tercero' => $lp['tercero'],
            'objeto' => $lp['objeto'],
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'botones' => '<div class="text-center">' . $editar . $detalles . $imprimir . $anular . $borrar . $dato . $abrir . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecordsFiltered,
    'recordsTotal' => $totalRecords,
];


echo json_encode($datos);
