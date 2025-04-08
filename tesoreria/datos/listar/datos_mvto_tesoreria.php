<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
include_once '../../../terceros.php';
include_once '../../../financiero/consultas.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];
$anulados = $_POST['anulados'];
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];
$dato = null;
$where = $_POST['search']['value'] != '' ? "AND (`ctb_doc`.`fecha` LIKE '%{$_POST['search']['value']}%' OR `ctb_doc`.`id_manu` LIKE '%{$_POST['search']['value']}%' OR  `tb_terceros`.`nom_tercero` LIKE '%{$_POST['search']['value']}%' OR `tb_terceros`.`nit_tercero` LIKE '%{$_POST['search']['value']}%')" : '';
if ($anulados == 1 || $_POST['search']['value'] != '') {
    $where .= " AND `ctb_doc`.`estado` >= 0";
} else {
    $where .= " AND `ctb_doc`.`estado` > 0";
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$erp = [];
if ($id_ctb_doc == '6') {
    try {
        $sql = "SELECT `id_transaccion` FROM `fac_pagos_erp` WHERE `estado` = 1";
        $rs = $cmd->query($sql);
        $erp = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
try {
    $sql = "SELECT `fecha_cierre` FROM `tb_fin_periodos` WHERE `id_modulo` = 56";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = !empty($fecha_cierre) ? $fecha_cierre['fecha_cierre'] : date("Y-m-d");
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
    // incrementar un dia a $fecha cierre
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre . '+1 day'));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {

    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`estado`
                , `nom_nominas`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `ctb_doc`.`id_tipo_doc`
                , `ctb_doc`.`id_vigencia`
                , `ctb_doc`.`doc_soporte`
            FROM
                `ctb_doc`
                LEFT JOIN `nom_nomina_pto_ctb_tes` 
                    ON (`ctb_doc`.`id_ctb_doc` = `nom_nomina_pto_ctb_tes`.`ceva`)
                LEFT JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                LEFT JOIN `tb_terceros`
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia $where) 
            ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    // contar el total de registros
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `ctb_doc`
                LEFT JOIN `nom_nomina_pto_ctb_tes` 
                    ON (`ctb_doc`.`id_ctb_doc` = `nom_nomina_pto_ctb_tes`.`ceva`)
                LEFT JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                LEFT JOIN `tb_terceros`
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    // contar el total de registros
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `ctb_doc`
                LEFT JOIN `nom_nomina_pto_ctb_tes` 
                    ON (`ctb_doc`.`id_ctb_doc` = `nom_nomina_pto_ctb_tes`.`ceva`)
                LEFT JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                LEFT JOIN `tb_terceros`
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia $where)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];
    // contar el total de registros
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$inicia = $_SESSION['vigencia'] . '-01-01';
$termina = $_SESSION['vigencia'] . '-12-31';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_soporte`, `id_factura_no`, `shash`, `referencia`, `fecha`
            FROM
                `seg_soporte_fno`
            WHERE (`fecha` BETWEEN '$inicia' AND '$termina')";
    $rs = $cmd->query($sql);
    $equivalente = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// consultar la fecha de cierre del periodo del módulo de presupuesto 
if (!empty($listappto)) {

    $ids = [];
    $id_cta = [];
    foreach ($listappto as $lp) {
        if ($lp['id_tercero'] !== null) {
            $ids[] = $lp['id_tercero'];
        }
        $id_cta[] = $lp['id_ctb_doc'];
    }
    $id_cta = implode(',', $id_cta);
    $ids = implode(',', $ids);
    $terceros = getTerceros($ids, $cmd);
    try {
        $sql = "SELECT 
                    `id_ctb_doc`
                    , SUM(`debito`) as `debito`
                    , SUM(`credito`) as `credito` 
                FROM `ctb_libaux` 
                WHERE `id_ctb_doc`IN ($id_cta) GROUP BY `id_ctb_doc`";
        $rs = $cmd->query($sql);
        $suma = $rs->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    foreach ($listappto as $lp) {
        $valor_total = 0;
        $id_ctb = $lp['id_ctb_doc'];
        $estado = $lp['estado'];
        $editar = $borrar = $imprimir = $detalles = $enviar = $dato = $cerrar = $anular = $doc_soporte = null;
        $tercero = $lp['nom_tercero'];
        $ccnit = $lp['nit_tercero'];
        if ($lp['tipo'] == 'N') {
            $enviar = '<button id ="enviar_' . $id_ctb . '" value="' . $lp['id_nomina'] . '" onclick="EnviarNomina(this)" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Procesar nómina (Soporte Electrónico)"><span class="fas fa-paper-plane fa-lg"></span></button>';
        }
        $disabled = $estado == 2 ? '' : 'disabled';
        if ($lp['doc_soporte'] == 1) {
            $key = array_search($id_ctb, array_column($equivalente, 'id_factura_no'));
            if ($key !== false && $equivalente[$key]['shash'] != '') {
                $doc_soporte = '<a onclick="VerSoporteElectronico(' . $equivalente[$key]['id_soporte'] . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="VER DOCUMENTO"><span class="far fa-file-pdf fa-lg"></span></a>';
            } else {
                $doc_soporte = '<button value="' . $id_ctb . '" onclick="EnviaDocumentoSoporte(this)" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="REPORTAR FACTURA" ' . $disabled . '><span class="fas fa-paper-plane fa-lg"></span></button>';
            }
        }
        // fin api terceros
        $key = array_search($id_ctb, array_column($suma, 'id_ctb_doc'));
        if ($key !== false) {
            $dif = $suma[$key]['debito'] - $suma[$key]['credito'];
            $valor_total = ($dif != 0) ? 'Error' : number_format($suma[$key]['credito'], 2, ',', '.');
        } else {
            $valor_total = number_format(0, 2, ',', '.');
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));

        if ((PermisosUsuario($permisos, 5601, 1) || PermisosUsuario($permisos, 5602, 1) || PermisosUsuario($permisos, 5603, 1) || PermisosUsuario($permisos, 5604, 1) || $id_rol == 1)) {
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Detalles" onclick="cargarListaDetallePagoEdit(' . $id_ctb . ')"><span class="fas fa-eye fa-lg"></span></a>';
        }
        if ((PermisosUsuario($permisos, 5601, 3) || PermisosUsuario($permisos, 5602, 3) || PermisosUsuario($permisos, 5603, 3) || PermisosUsuario($permisos, 5604, 3) || $id_rol == 1)) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb modificar"  text="' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((PermisosUsuario($permisos, 5601, 4) || PermisosUsuario($permisos, 5602, 4) || PermisosUsuario($permisos, 5603, 4) || PermisosUsuario($permisos, 5604, 4) || $id_rol == 1)) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroTec(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ((PermisosUsuario($permisos, 5601, 5) || PermisosUsuario($permisos, 5602, 5) || PermisosUsuario($permisos, 5603, 5) || PermisosUsuario($permisos, 5604, 5) || $id_rol == 1)) {
            if ($estado == 1) {
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" title="Cerrar"><span class="fas fa-lock fa-lg"></span></a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="abrirDocumentoTes(' . $id_ctb . ')" title="Abrir"><span class="fas fa-unlock fa-lg"></span></a>';
            }
            $anular = '<a value="' . $id_ctb . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" onclick="anularDocumentoTes(' . $id_ctb . ')" title="Anular"><span class="fas fa-ban fa-lg"></span></a>';
            $key = array_search($id_ctb, array_column($erp, 'id_transaccion'));
            if ($fecha >= $fecha_cierre || $key !== false) {
                $anular = null;
                $cerrar = null;
            }
        }
        if ((PermisosUsuario($permisos, 5601, 6) || PermisosUsuario($permisos, 5602, 6) || PermisosUsuario($permisos, 5603, 6) || PermisosUsuario($permisos, 5604, 6) || $id_rol == 1)) {
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoTes(' . $lp['id_ctb_doc'] . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
        }

        if ($estado >= 2) {
            $editar = null;
            $borrar = null;
        }
        if ($estado == '0') {
            $editar = null;
            $borrar = null;
            $acciones = null;
            $enviar = null;
            $cerrar = null;
            $anular = null;
            $detalles = null;
            $dato = '<span class="badge badge-pill badge-secondary">Anulado</span>';
        }
        $data[] = [
            'numero' => $lp['id_manu'],
            'fecha' => $fecha,
            'ccnit' => $ccnit,
            'tercero' => $tercero,
            'valor' => '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $borrar . $imprimir . $enviar . $dato . $cerrar . $doc_soporte . $anular . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecordsFilter,
    'recordsTotal' => $totalRecords,
];


echo json_encode($datos);
