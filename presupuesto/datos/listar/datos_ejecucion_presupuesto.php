<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
// Div de acciones de la lista
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search'] ?? '';
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (pto_documento.id_manu LIKE '%$search_value%' OR pto_documento.objeto LIKE '%$search_value%' OR pto_documento.fecha LIKE '%$search_value%' OR afec.dispon LIKE '$search_value' )";
} else {
    $buscar = '';
}
try {
    //$sql = "SELECT id_pto_doc,id_manu,fecha,objeto FROM pto_documento WHERE id_pto_presupuestos=$id_pto_presupuestos AND tipo_doc='CDP' ORDER BY id_manu DESC LIMIT $start, $length";
    $sql = "SELECT 
                `pto_cdp`.`id_pto_cdp`
                , `pto_cdp`.`id_manu`
                , `pto_cdp`.`fecha`
                , `pto_cdp`.`objeto`
                , `pto_cdp`.`estado`
                , `t2`.`val_cdp`
                , `t2`.`val_lib_cdp`
                , `t2`.`val_crp`
                , `t2`.`val_lib_crp`
            FROM 
                `pto_cdp`
            LEFT JOIN
                (SELECT 
                    `id_pto_cdp`
                    , SUM(`val_cdp`) AS `val_cdp`
                    , SUM(`val_lib_cdp`) AS `val_lib_cdp`
                    , SUM(`val_crp`) AS `val_crp`
                    , SUM(`val_lib_crp`)AS `val_lib_crp`
                FROM
                    (SELECT
                        `pto_cdp`.`id_pto_cdp`
                        , `detalles`.`val_cdp`
                        , `detalles`.`val_lib_cdp`
                        , `detalles`.`val_crp`
                        , `detalles`.`val_lib_crp`
                    FROM
                        `pto_cdp`
                    LEFT JOIN 
                        (SELECT
                            `pto_cdp_detalle`.`id_pto_cdp`
                            , `pto_cdp_detalle`.`valor` AS `val_cdp`
                            , `pto_cdp_detalle`.`valor_liberado` AS `val_lib_cdp`
                            , `pto_crp_detalle`.`valor` AS `val_crp`
                            , `pto_crp_detalle`.`valor_liberado` AS `val_lib_crp`
                        FROM
                            `pto_cdp_detalle`
                        LEFT JOIN `pto_crp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)) AS `detalles`
                    ON (`pto_cdp`.`id_pto_cdp` = `detalles`.`id_pto_cdp`)
                WHERE `pto_cdp`.`id_pto` = $id_pto_presupuestos) AS `t1`
                GROUP BY `id_pto_cdp`) AS `t2` 
                ON(`t2`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
            LIMIT $start, $length";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
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
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT `fecha_cierre` FROM `tb_fin_periodos` WHERE `id_modulo` = 4";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $anular = $dato = $borrar = $imprimir = null;
        $id_pto = $lp['id_pto_cdp'];
        // Sumar el valor del cdp de la tabla id_pto_mtvo
        $valor_cdp = number_format($lp['val_cdp'], 2, ',', '.');
        $valor_cdp_lib = number_format($lp['val_lib_cdp'], 2, ',', '.');
        $valor_crp = number_format($lp['val_crp'], 2, ',', '.');
        $valor_crp_lib = number_format($lp['val_lib_crp'], 2, ',', '.');
        $cxregistrar = $lp['val_cdp'] - $lp['val_lib_cdp'] - $lp['val_crp'] + $lp['val_lib_crp'];
        $xregistrar = number_format($cxregistrar, 2, ',', '.');
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if (!($fecha <= $fecha_cierre) && (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1)) {
            $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
        }
        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            $registrar = '<a value="' . $id_pto . '" onclick="CargarFormularioCrpp(' . $id_pto . ')" class="text-blue " role="button" title="Detalles"><span class="badge badge-pill badge-primary">Registrar</span></a>';

            if ($cxregistrar  == 0) {
                $registrar = '--';
                $anular = null;
            }
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" onclick="verLiquidarCdp(' . $id_pto . ')" class="dropdown-item sombra" href="#">Ver historial</a>
            ' . $anular . '
            </div>';
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
        if ($lp['estado'] == 0) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $acciones = null;
            $imprimir = null;
            $dato = 'Anulado';
        }
        if ($lp['estado'] >= 2) {
            $borrar = null;
            $editar = null;
        }
        $data[] = [
            'numero' => $lp['id_manu'],
            'fecha' => $fecha,
            'objeto' => $lp['objeto'],
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'liberado' =>  '<div class="text-right">' . $valor_cdp_lib . '</div>',
            'xregistrar' =>  '<div class="text-right">' . $xregistrar  . '</div>',
            'accion' => '<div class="text-center">' . $registrar . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $imprimir . $acciones . $dato . $borrar . '</div>',
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
