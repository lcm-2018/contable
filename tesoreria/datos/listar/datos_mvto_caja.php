<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$dato = null;
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_caja_const`.`id_caja_const`
                , `pto_actos_admin`.`nombre` AS `acto`
                , `tes_caja_const`.`num_acto`
                , `tes_caja_const`.`nombre_caja`
                , `tes_caja_const`.`fecha_ini`
                , `tes_caja_const`.`fecha_acto`
                , `tes_caja_const`.`valor_total`
                , `tes_caja_const`.`valor_minimo`
                , `tes_caja_const`.`num_poliza`
                , `tes_caja_const`.`porcentaje`
                , `tes_caja_const`.`estado`
            FROM
                `tes_caja_const`
                INNER JOIN `pto_actos_admin` 
                    ON (`tes_caja_const`.`id_tipo_acto` = `pto_actos_admin`.`id_acto`)
            WHERE (`tes_caja_const`.`fecha_ini` BETWEEN '$vigencia-01-01' AND '$vigencia-12-31')";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT `fecha_cierre` FROM `tb_fin_periodos` WHERE `id_modulo`= 6";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listado)) {
    foreach ($listado as $lp) {
        $id_ctb = $lp['id_caja_const'];
        $estado = $lp['estado'];
        $fecha = date('Y-m-d', strtotime($lp['fecha_ini']));
        $editar = $detalles = $acciones = $borrar = $responsable = $rubros = null;
        if ($fecha <= $fecha_cierre) {
            $anular = null;
            $cerrar = null;
        } else {
            $anular = '<a value="' . $id_ctb . '" class="dropdown-item sombra " href="#" onclick="anularDocumentoTes(' . $id_ctb . ');">Anulación</a>';
        }
        if ((PermisosUsuario($permisos, 5604, 2) || $id_rol == 1) && $estado == '1') {
            $responsable = '<a value="' . $id_ctb . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" href="#" onclick="cargarResponsableCaja(' . $id_ctb . ',0);" title="Gestionar Responsables"><span class="fas fa-user-tie fa-lg"></span></a>';
            $rubro = '<a value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" href="#" onclick="cargarRubrosCaja(' . $id_ctb . ',0);" title="Gestionar Rubros"><span class="far fa-list-alt fa-lg"></span></a>';
        }
        if ((PermisosUsuario($permisos, 5604, 3) || $id_rol == 1)) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editarCaja"  text="' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Detalles" onclick="cargarListaDetalleCajaEdit(' . $id_ctb . ')"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoCaja()" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            // Acciones teniendo en cuenta el tipo de rol
            //si es lider de proceso puede abrir o cerrar documentos
        }
        if ((PermisosUsuario($permisos, 5604, 4) || $id_rol == 1)) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroCaja(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($estado == '1') {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoTes(' . $id_ctb . ')" href="#">Abrir documento</a>';
            }
        }
        if ($estado == '1') {
            $estado = '<span class="badge badge-success">Abierto</span>';
        }
        if ($estado == '2') {
            $editar = null;
            $borrar = null;
            $estado = '<span class="badge badge-secondary">Cerrado</span>';
        }
        if ($estado == '0') {
            $editar = null;
            $borrar = null;
            $imprimir = null;
            $estado = '<span class="badge badge-danger">Anulado</span>';
        }
        $data[] = [
            'acto' => $lp['acto'],
            'num_acto' => $lp['num_acto'],
            'nombre_caja' => $lp['nombre_caja'],
            'fecha_ini' => $lp['fecha_ini'],
            'fecha_acto' => $lp['fecha_acto'],
            'valor_total' => '<div class="text-right">' . pesos($lp['valor_total']) . '</div>',
            'valor_minimo' => '<div class="text-right">' . pesos($lp['valor_minimo']) . '</div>',
            'num_poliza' => $lp['num_poliza'],
            'porcentaje' => $lp['porcentaje'],
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $borrar . $imprimir  . $responsable . $rubro . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
