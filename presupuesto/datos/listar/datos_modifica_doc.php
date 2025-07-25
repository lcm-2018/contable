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

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);

// Div de acciones de la lista
$tipo_doc = $_POST['id_pto_doc'];
$id_pto_presupuestos = $_POST['id_pto_ppto'];
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $sql = "SELECT
                `pto_mod`.`id_pto_mod`
                , `pto_mod`.`id_pto`
                , `pto_mod`.`id_tipo_acto`
                , `pto_mod`.`id_tipo_mod`
                , `pto_tipo_mvto`.`nombre`
                , `pto_mod`.`fecha`
                , `pto_mod`.`id_manu`
                , `pto_mod`.`objeto`
                , `pto_mod`.`estado`
                , `pto_actos_admin`.`nombre` AS `acto`
                , `pto_mod`.`numero_acto`
            FROM
                `pto_mod`
                INNER JOIN `pto_tipo_mvto` 
                    ON (`pto_mod`.`id_tipo_mod` = `pto_tipo_mvto`.`id_tmvto`)
                INNER JOIN `pto_actos_admin` 
                    ON (`pto_mod`.`id_tipo_acto` = `pto_actos_admin`.`id_acto`)
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE `pto_mod`.`id_tipo_mod` = $tipo_doc AND `pto_presupuestos`.`id_vigencia` = $id_vigencia
            ORDER BY `pto_mod`.`id_manu` ASC";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `pto_mod_detalle`.`id_pto_mod`
                , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                , SUM(`pto_mod_detalle`.`valor_cred`) AS `credito`
                , `pto_presupuestos`.`id_tipo`
            FROM
                `pto_mod_detalle`
                INNER JOIN `pto_mod` 
                    ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                INNER JOIN `pto_cargue`
                    ON (`pto_mod_detalle`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_presupuestos`
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_mod`.`id_tipo_mod` = $tipo_doc AND `pto_mod`.`estado` >= 1)
            GROUP BY `pto_mod_detalle`.`id_pto_mod`, `pto_presupuestos`.`id_tipo`";
    $rs = $cmd->query($sql);
    $valores = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$num = 0;
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $detalles = $cerrar = $editar = $borrar = $anular = $imprimir = '';
        $id_pto = $lp['id_pto_mod'];
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        $key = array_search($id_pto, array_column($valores, 'id_pto_mod'));
        if ($key !== false) {
            // filtrar por id_pto_mod para obtener todos los valores 
            $filtro = [];
            $filtro = array_filter($valores, function ($val) use ($id_pto) {
                return $val['id_pto_mod'] == $id_pto;
            });
            $valor1 = 0;
            $valor2 = 0;
            foreach ($filtro as $f) {
                $tipo_pto = $f['id_tipo'];
                if ($tipo_pto == '1' && ($tipo_doc == '3' || $tipo_doc == '2')) {
                    $valor1 += $f['debito'] - $f['credito'];
                    $valor2 += 0;
                } else if ($tipo_pto == '2' && ($tipo_doc == '3' || $tipo_doc == '2')) {
                    $valor1 += 0;
                    $valor2 += $f['debito'] - $f['credito'];
                } else {
                    $valor1 += $f['debito'];
                    $valor2 += $f['credito'];
                }
            }
        } else {
            $valor1 = 0;
            $valor2 = 0;
        }
        $diferencia = $valor1 - $valor2;
        if (PermisosUsuario($permisos, 5401, 1) || $id_rol == 1) {
            $detalles = '<a value="' . $id_pto . '" onclick="cargarListaDetalleMod(' . $id_pto . ')" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            if ($lp['estado'] == '2') {
                $cerrar = '<button value="' . $id_pto . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb abrir" onclick="abrirDocumentoMod(' . $id_pto . ')"><span class="fas fa-lock fa-lg"></span></button>';
            } else {
                $cerrar = '<button value="' . $id_pto . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb cerrar" onclick="cerrarMod(' . $id_pto . ')"><span class="fas fa-unlock fa-lg"></span></button>';
            }
        }
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            $editar = '<button id ="eliminar_' . $id_pto . '" value="' . $id_pto . '" onclick="editarModPresupuestal(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-pen fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<button id ="eliminar_' . $id_pto . '" value="' . $id_pto . '" onclick="eliminarModPresupuestal(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1) {
            $anular = '<button text="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" onclick="anulacionPtoMod(this);"><span class="fas fa-ban fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) {
            $imprimir = '<button value="' . $id_pto . '" onclick="imprimirFormatoMod(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-print fa-lg"></span></button>';
        }
        if ($tipo_doc == '4' || $tipo_doc == '5') {
            $diferencia = 0;
        }
        if ($diferencia == 0) {
            $valor2 = number_format($valor2, 2, '.', ',');
            $estado = '<div class="text-right" ' . $valor1 . '-' . $valor2 . '>' . $valor2 . '</div>';
        } else {
            $estado = '<div class="text-center"><span class="label text-danger">Incorrecto</span></div>';
        }

        if ($lp['estado'] == '0') {
            $estado = '<div class="text-center"><span class="label text-secondary">Anulado</span></div>';
        }
        if ($fecha <= $fecha_cierre || $lp['estado'] == '0') {
            $cerrar = $editar = $borrar = $anular = '';
        }
        if ($lp['estado'] == '2') {
            $editar = $borrar = $anular = '';
        }

        $num = $lp['id_manu'];
        $data[] = [
            'num' => $num,
            'fecha' => $fecha,
            'documento' => $lp['acto'],
            'numero' => $lp['numero_acto'],
            'valor' => $estado,
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $imprimir . $cerrar . $anular . $borrar . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
