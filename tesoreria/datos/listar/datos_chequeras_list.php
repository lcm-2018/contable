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
                `fin_chequeras`.`fecha`
                , `fin_chequeras`.`id_chequera`
                , `tes_cuentas`.`nombre`
                , `fin_chequeras`.`numero` 
                , `fin_chequeras`.`inicial` AS `en_uso`
                , `fin_chequeras`.`maximo` AS `final`
                , `fin_chequeras`.`contador`
                , `tb_bancos`.`nom_banco`
                , `tb_bancos`.`id_banco`
            FROM
                `fin_chequeras`
                INNER JOIN `tes_cuentas` 
                    ON (`fin_chequeras`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $editar = $detalles = $acciones = $borrar = null;
        $fecha = date('Y-m-d', strtotime($lp['fecha']));

        if (PermisosUsuario($permisos, 5608, 3) || $id_rol == 1) {
            $id_ctb = $lp['id_chequera'];
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosChequera(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            //si es lider de proceso puede abrir o cerrar documentos

        }
        if (PermisosUsuario($permisos, 5608, 3) || $id_rol == 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarChequera(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
            </div>';
        }
        $contador = $lp['contador'] == '' ? $lp['en_uso'] : $lp['contador'];
        $acciones = null;
        $data[] = [

            'fecha' => $fecha,
            'banco' => $lp['nom_banco'],
            'cuenta' => $lp['nombre'],
            'numero' => $lp['numero'],
            'inicial' => $lp['en_uso'] . ' - ' . $lp['final'],
            'en_uso' => $contador,
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar  . $acciones . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
