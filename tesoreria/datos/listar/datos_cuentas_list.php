<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_bancos`.`nom_banco`
                , `tb_bancos`.`cod_sia`
                , `tes_tipo_cuenta`.`tipo_cuenta`
                , `tes_cuentas`.`nombre`
                , `tes_cuentas`.`numero`
                , `tes_cuentas`.`id_cuenta`
                , `ctb_pgcp`.`cuenta` AS `cta_contable`
                , `tes_cuentas`.`estado`
                , `tes_cuentas`.`id_tes_cuenta`
                , `fin_cod_fuente`.`nombre` AS `fuente`
                , `fin_cod_fuente`.`codigo` AS `codigo_fte`
            FROM
                `tes_cuentas`
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                LEFT JOIN `tes_tipo_cuenta` 
                    ON (`tes_cuentas`.`id_tipo_cuenta` = `tes_tipo_cuenta`.`id_tipo_cuenta`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `fin_cod_fuente` 
                    ON (`tes_cuentas`.`id_fte` = `fin_cod_fuente`.`id`)";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $editar = $borrar = $acciones = $cerrar = null;
        $id_ctb = $lp['id_tes_cuenta'];
        if ($lp['estado'] == 1) {
            $estado = '<span class="badge badge-success">Activa</span>';
            if (PermisosUsuario($permisos, 5607, 3) || $id_rol == 1) {
                $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosCuenta(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                //si es lider de proceso puede abrir o cerrar documentos
            }
            if (PermisosUsuario($permisos, 5607, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $id_ctb . '" onclick="eliminarCuentaBancaria(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarCuentaBco(' . $id_ctb . ')" href="#">Desactivar cuenta</a>';
            }
        } else {
            $estado = '<span class="badge badge-secondary">Inactiva</span>';
            if (PermisosUsuario($permisos, 5607, 4) || $id_rol == 1) {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirCuentaBco(' . $id_ctb . ')" href="#">Activar cuenta</a>';
            }
        }

        $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $cerrar . '
            </div>';

        $data[] = [

            'banco' => $lp['nom_banco'],
            'tipo' => $lp['tipo_cuenta'],
            'nombre' => $lp['nombre'],
            'numero' => $lp['numero'],
            'sia' => $lp['cod_sia'],
            'fuente' => $lp['fuente'] != '' ? $lp['fuente'] . ' (' . $lp['codigo_fte'] . ')' : '',
            'cuenta' => $lp['cta_contable'],
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar  . $acciones . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
