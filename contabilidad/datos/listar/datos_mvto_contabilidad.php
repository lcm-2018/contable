<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];
$id_vigencia = $_SESSION['id_vigencia'];
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

$where = $_POST['search']['value'] != '' ? "AND `ctb_doc`.`fecha` LIKE '%{$_POST['search']['value']}%' OR `tb_terceros`.`nom_tercero` LIKE '%{$_POST['search']['value']}%' OR  `pto_crp`.`id_manu` LIKE '%{$_POST['search']['value']}%' OR `ctb_doc`.`id_manu` LIKE '%{$_POST['search']['value']}%'" : '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp`.`id_manu` AS `id_crp`
                , `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_factura`.`id_tipo_doc` AS `tipo`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`estado`
                , `ctb_fuente`.`cod`
                , `ctb_fuente`.`nombre`
                , `pag`.`id_ctb_doc` AS `pag`
            FROM
                `ctb_doc`
                LEFT JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                LEFT JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN `ctb_factura` 
                    ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN 
                    (SELECT
                        `pto_cop_detalle`.`id_ctb_doc`
                    FROM
                        `pto_pag_detalle`
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    GROUP BY `pto_cop_detalle`.`id_ctb_doc`) AS `pag`
                    ON (`pag`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia $where)
            GROUP BY `ctb_doc`.`id_ctb_doc`
            ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `ctb_doc`
                LEFT JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                LEFT JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `ctb_doc`
                LEFT JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                LEFT JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia $where)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT fecha_cierre FROM tb_fin_periodos WHERE id_modulo = 55";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = !empty($fecha_cierre) ? $fecha_cierre['fecha_cierre'] : date("Y-m-d");
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
    // incrementar un dia a $fecha cierre
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre . '+1 day'));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto la diferencia de la suma debito credito de la tabla ctb_libaux
try {
    $sql = "SELECT
                `id_ctb_doc`
                ,SUM(`debito`) AS `debito`
                , SUM(`credito`) AS `credito`
                , SUM(`debito` - `credito`) AS `diferencia`
            FROM
                `ctb_libaux`
            GROUP BY `id_ctb_doc`";
    $rs = $cmd->query($sql);
    $diferencias = $rs->fetchAll();
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
$ids = [];
foreach ($listappto as $lp) {
    if ($lp['id_tercero'] != '') {
        $ids[] = $lp['id_tercero'];
    }
}
$ids = implode(',', $ids);
$terceros = getTerceros($ids, $cmd);
$data = [];
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $valor_debito = 0;
        $id_ctb = $lp['id_ctb_doc'];
        $estado = $lp['estado'];
        $anular = $dato = null;
        $tercero = $lp['nom_tercero'] != '' ? $lp['nom_tercero'] : '---';
        // consultar la diferencia en array diferencias
        $key = array_search($id_ctb, array_column($diferencias, 'id_ctb_doc'));
        $editar = $detalles = $borrar = $imprimir = $enviar = $acciones = $dato = $imprimir = null;
        if ($key  !== false) {
            $valor_debito = $diferencias[$key]['debito'];
            $dif = $diferencias[$key]['diferencia'];
        } else {
            $valor_debito = 0;
            $dif = 0;
        }
        if ($dif != 0) {
            $valor_total = 'Error';
        } else {
            $valor_total = number_format($valor_debito, 2, ',', '.');
        }
        // Consulto el numero de registro presupuestal asociado al documento
        $id_manu_rp = $lp['id_crp'] != '' ? $lp['id_crp'] : '-';

        $fecha = date('Y-m-d', strtotime($lp['fecha']));

        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha > $fecha_cierre) {
            $anular = null;
            $cerrar = null;
        } else if ($lp['pag'] != '') {
            $anular = null;
            $cerrar = null;
        } else {
            //$anular = '<a value="' . $id_ctb . '" class="dropdown-item sombra " href="#" onclick="anularDocumentoCont(' . $id_ctb . ');">Anulación</a>';
            if ($estado == '1') {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="CierraDocCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoCtb(' . $id_ctb . ')" href="#">Abrir documento</a>';
            }
        }
        $base = base64_encode($id_ctb . '|' . $id_ctb_doc);
        if (PermisosUsuario($permisos, 5501, 3)  || $id_rol == 1) {
            $editar = '<a class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar" text="' . $id_ctb . '"><span class="fas  fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a text ="' . $base . '" onclick="cargarListaDetalle(this)" class="btn btn-outline-warning btn-sm btn-circle shadow-gb"  title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoDoc(' . $lp['id_ctb_doc'] . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
           ' . $cerrar . '
           ' . $anular . '
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
            </div>';
        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }
        if (PermisosUsuario($permisos, 5501, 4)  || $id_rol == 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroDoc(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        if ($estado == 2) {
            $editar = null;
            $borrar = null;
        }
        $enviar = null;
        if ($fecha < '2023-02-16') {
            $enviar = null;
        } else {
            if ($lp['tipo'] == 3) {
                $key = array_search($id_ctb, array_column($equivalente, 'id_factura_no'));
                if ($key !== false) {
                    $enviar = '<a onclick="VerSoporteElectronico(' . $equivalente[$key]['id_soporte'] . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="VER DOCUMENTO"><span class="far fa-file-pdf fa-lg"></span></a>';
                } else {
                    $enviar = '<a id="enviaSoporte" onclick="EnviaDocumentoSoporte(' . $id_ctb . ')" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="REPORTAR FACTURA"><span class="fas fa-paper-plane fa-lg"></span></a>';
                }
            }
        }
        if ($estado == 0) {
            $editar = null;
            $borrar = null;
            $imprimir = null;
            $acciones = null;
            $enviar = null;
            $dato = '<span class="badge badge-pill badge-danger">Anulado</span>';
        }

        $data[] = [

            'numero' => $lp['id_manu'],
            'rp' =>  $id_manu_rp,
            'fecha' => $fecha,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $borrar . $imprimir  . $enviar . $acciones .  $dato . '</div>',
        ];
    }
}
$cmd = null;
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter

];


echo json_encode($datos);
