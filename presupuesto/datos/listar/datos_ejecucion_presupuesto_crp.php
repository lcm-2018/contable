<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/consultas.php';
// Llega el id del presupuesto que se esta listando
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = isset($_POST['search']) ? $_POST['search'] : '';
$anulados = isset($_POST['anulados']) ? $_POST['anulados'] : 0;
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (`pto_crp`.`id_manu` LIKE '%$search_value%' OR `detalle`.`id_cdp` LIKE '%$search_value%' OR `pto_crp`.`objeto` LIKE '%$search_value%' OR `pto_crp`.`fecha` LIKE '%$search_value%' OR `pto_crp`.`num_contrato` LIKE '%$search_value%' OR `tb_terceros`.`nom_tercero` LIKE '%$search_value%' OR `tb_terceros`.`nit_tercero` LIKE '%$search_value%')";
} else {
    $buscar = '';
}
if ($anulados == 1 || !empty($search_value)) {
    $buscar .= " AND `pto_crp`.`estado` >= 0";
} else {
    $buscar .= " AND `pto_crp`.`estado` >= 0";
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);


//----------- filtros--------------------------

$andwhere = " ";

if (isset($_POST['id_manu']) && $_POST['id_manu']) {
    $andwhere .= " AND pto_crp.id_manu LIKE '%" . $_POST['id_manu'] . "%'";
}
if (isset($_POST['id_manucdp']) && $_POST['id_manucdp']) {
    $andwhere .= " AND detalle.id_cdp LIKE '%" . $_POST['id_manucdp'] . "%'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $andwhere .= " AND pto_crp.fecha BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['contrato']) && $_POST['contrato']) {
    $andwhere .= " AND pto_crp.num_contrato LIKE '%" . $_POST['contrato'] . "%'";
}
if (isset($_POST['ccnit']) && $_POST['ccnit']) {
    $andwhere .= " AND tb_terceros.nit_tercero LIKE '%" . $_POST['ccnit'] . "%'";
}
if (isset($_POST['tercero']) && $_POST['tercero']) {
    $andwhere .= " AND tb_terceros.nom_tercero LIKE '%" . $_POST['tercero'] . "%'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    if ($_POST['estado'] == "-1") {
        $andwhere .= " AND pto_crp.estado>=" . $_POST['estado'];
    } else {
        $andwhere .= " AND pto_crp.estado=" . $_POST['estado'];
    }
}

try {
    $sql = "SELECT
                `pto_crp`.`id_pto_crp`
                , `pto_crp`.`id_pto`
                , `pto_crp`.`fecha`
                , `pto_crp`.`id_manu`
                , `pto_crp`.`id_tercero_api`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `pto_crp`.`objeto`
                , `pto_crp`.`num_contrato`
                , `pto_crp`.`estado`
                , `detalle`.`debito`
                , `detalle`.`credito`
                , `detalle`.`id_cdp`
                , `cop`.`saldo`
            FROM
                `pto_crp`
            LEFT JOIN 
                (SELECT
                    `pto_crp_detalle`.`id_pto_crp`
                    , SUM(`pto_crp_detalle`.`valor`) AS `debito`
                    , SUM(`pto_crp_detalle`.`valor_liberado`) AS `credito`
                    , `pto_cdp`.`id_manu` AS `id_cdp`
                FROM
                    `pto_crp_detalle`
                    LEFT JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    LEFT JOIN `pto_cdp` 
                        ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)  
                GROUP BY `pto_crp_detalle`.`id_pto_crp`) AS `detalle`
                ON (`pto_crp`.`id_pto_crp` = `detalle`.`id_pto_crp`)
            LEFT JOIN 
                (SELECT
                    SUM(`pto_cop_detalle`.`valor`) - SUM(`pto_cop_detalle`.`valor_liberado`) AS `saldo`
                    , `pto_crp_detalle`.`id_pto_crp`
                FROM
                    `pto_cop_detalle`
                    INNER JOIN `pto_crp_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                    INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `ctb_fuente` 
                            ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                WHERE (`ctb_doc`.`estado` > 0 AND `ctb_fuente`.`cod` <> 'CXPA' )
                GROUP BY `pto_crp_detalle`.`id_pto_crp`) AS `cop`
                ON (`pto_crp`.`id_pto_crp` = `cop`.`id_pto_crp`)
            LEFT JOIN `tb_terceros`
                ON (`pto_crp`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`id_pto` = $id_pto_presupuestos) $buscar $andwhere
            ORDER BY `id_manu` DESC 
            LIMIT $start, $length";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// obtener el numero total de registros de la anterior consulta
try {
    $sql = "SELECT COUNT(*) AS `total` FROM `pto_crp` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = !empty($total['total']) ?  $total['total'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cmd = null;

// consultar la fecha de cierre del periodo del módulo de presupuesto 
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_crp'];
        $anular = $dato = $borrar = $imprimir = $detalles = $abrir = null;
        // Sumar el valor del crp de la tabla id_pto_mtvo
        $valor_crp = $lp['debito'];
        $valor_crp = number_format($valor_crp, 2, ',', '.');
        $tercero  = $lp['nom_tercero'];
        $ccnit = $lp['nit_tercero'];
        if ($lp['id_tercero_api'] == 0) {
            $tercero = 'NOMINA DE EMPLEADOS';
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor o igual a $fecha_cierre no se puede editar ni eliminar
        $info = base64_encode($id_pto . '|crp');
        $id_cdp = $lp['id_cdp'];
        if (PermisosUsuario($permisos, 5401, 1) || $id_rol == 1) {
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="CargarListadoCrpp(' . $id_pto . ')" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_pto . '" onclick="eliminarCrpp(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ($fecha > $fecha_cierre && (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1)) {
            $anular = '<button text="' . $info . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Anular" onclick="anulacionPto(this);"><span class="fas fa-ban fa-lg"></span></button>';
        }
        if ($fecha > $fecha_cierre && (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1)) {
            if ($lp['estado'] == 2) {
                $abrir = '<a onclick="abrirCrp(' . $id_pto . ')" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb " title="Abrir Registro Presupuestal"><span class="fas fa-lock fa-lg"></span></a>';
            } else {
                $abrir = '<a onclick="CierraCrp(' . $id_pto . ')" class="btn btn-outline-info btn-sm btn-circle shadow-gb " title="Cerrar Registro Presupuestal"><span class="fas fa-unlock fa-lg"></span></a>';
            }
        }
        if ($lp['saldo'] > 0) {
            $anular = null;
            $abrir = null;
        }
        if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) {
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoCrp(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-print fa-lg" ></span></a>';
        }
        // si estado es 0 quiere decir que el crp esta anulado
        if ($lp['estado'] == 0) {
            $borrar = null;
            $editar = null;
            $anular = null;
            $detalles = null;
            $abrir = null;
            $dato = '<span class="badge badge-pill badge-secondary">Anulado</span>';
        }
        if ($lp['estado'] >= 2) {
            $borrar = null;
            $editar = null;
        }
        $data[] = [
            'numero' => $lp['id_manu'],
            'cdp' => $id_cdp,
            'fecha' => $fecha,
            'contrato' => $lp['num_contrato'],
            'ccnit' => $ccnit,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_crp . '</div>',
            'botones' => '<div class="text-center">' . $editar . $detalles . $imprimir . $abrir . $anular . $borrar . $dato . '</div>',

        ];
    }
} else {
    $data = [];
}
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
];


echo json_encode($datos);
