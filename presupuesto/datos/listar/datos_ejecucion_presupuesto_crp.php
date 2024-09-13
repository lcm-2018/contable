<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
// Llega el id del presupuesto que se esta listando
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = isset($_POST['search']) ? $_POST['search'] : '';
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (`pto_crp`.`id_manu` LIKE '%$search_value%' OR `pto_crp`.`objeto` LIKE '%$search_value%' OR `pto_crp`.`fecha` LIKE '%$search_value%')";
} else {
    $buscar = '';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp`.`id_pto_crp`
                , `pto_crp`.`id_pto`
                , `pto_crp`.`fecha`
                , `pto_crp`.`id_manu`
                , `pto_crp`.`id_tercero_api`
                , `pto_crp`.`objeto`
                , `pto_crp`.`num_contrato`
                , `pto_crp`.`estado`
                , `detalle`.`debito`
                , `detalle`.`credito`
                , `detalle`.`id_cdp`
                , `detalle`.`id_cop`
            FROM
                `pto_crp`
            LEFT JOIN 
                (SELECT
                    `pto_crp_detalle`.`id_pto_crp`
                    , SUM(`pto_crp_detalle`.`valor`) AS `debito`
                    , SUM(`pto_crp_detalle`.`valor_liberado`) AS `credito`
                    , `pto_cdp`.`id_manu` AS `id_cdp`
                    , `pto_cop_detalle`.`id_pto_crp_det` AS `id_cop`
                FROM
                    `pto_crp_detalle`
                    LEFT JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    LEFT JOIN `pto_cdp` 
                        ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)  
                    LEFT JOIN `pto_cop_detalle`
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                GROUP BY `pto_crp_detalle`.`id_pto_crp`) AS `detalle`
            ON (`pto_crp`.`id_pto_crp` = `detalle`.`id_pto_crp`)
            WHERE (`id_pto` = $id_pto_presupuestos)
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
    $id_t = [];
    foreach ($listappto as $rp) {
        $id_t[] = $rp['id_tercero_api'];
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_crp'];
        $dato = null;
        // Sumar el valor del crp de la tabla id_pto_mtvo
        $valor_crp = $lp['debito'] - $lp['credito'];
        $valor_crp = number_format($valor_crp, 2, ',', '.');
        $key = array_search($lp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        if ($key !== false) {
            $tercero = $terceros[$key]['nom_tercero'];
            $ccnit = $terceros[$key]['nit_tercero'];
        } else {
            $tercero = '---';
            $ccnit = '---';
        }
        // fin api terceros
        if ($lp['id_tercero_api'] == 0) {
            $tercero = 'NOMINA DE EMPLEADOS';
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
        } else {
            $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
        }

        $id_cdp = $lp['id_cdp'];
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_pto . '" onclick="CargarListadoCrpp(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" onclick="imprimirFormatoCrp(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-print fa-lg" ></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $anular . '
            <a value="' . $id_pto . '" class="dropdown-item sombra " href="#">Ver historial</a>
            </div>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_pto . '" onclick="eliminarCrpp(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        if ($lp['id_cop'] != '') {
            $borrar = null;
            $editar = null;
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra " href="#">Ver historial</a>
            </div>';
        }
        // si estado es 5 quiere decir que el crp esta anulado
        if ($lp['estado'] == 0) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $acciones = null;
            $dato = 'Anulado';
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
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . $dato . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
];


echo json_encode($datos);
