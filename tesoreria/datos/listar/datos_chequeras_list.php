<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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
                seg_fin_chequeras.fecha
                , seg_fin_chequeras.id_chequera
                , seg_tes_cuentas.nombre
                , seg_fin_chequeras.numero
                , seg_fin_chequeras.inicial
                , seg_fin_chequeras.maximo AS final
                , consecutivo.en_uso
                , tb_bancos.nom_banco
            FROM
                seg_fin_chequeras
                INNER JOIN seg_tes_cuentas ON (seg_fin_chequeras.id_cuenta = seg_tes_cuentas.id_tes_cuenta)
                INNER JOIN tb_bancos ON (seg_tes_cuentas.id_banco = tb_bancos.id_banco)

            LEFT JOIN (
                SELECT
                    MAX(seg_fin_chequera_cont.contador) AS en_uso
                    ,seg_fin_chequera_cont.id_chequera 
                FROM
                    seg_fin_chequera_cont
                INNER JOIN seg_fin_chequeras ON (seg_fin_chequera_cont.id_chequera = seg_fin_chequeras.id_chequera)
                )consecutivo  ON (seg_fin_chequeras.id_chequera=consecutivo.id_chequera)
            ORDER BY seg_fin_chequeras.fecha ASC;";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $fecha = date('Y-m-d', strtotime($lp['fecha']));

        if ((intval($permisos['editar'])) === 1) {
            $id_ctb = $lp['id_chequera'];
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosChequera(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            //si es lider de proceso puede abrir o cerrar documentos

        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarChequera(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
            </div>';
        } else {
            $borrar = null;
        }

        $data[] = [

            'fecha' => $fecha,
            'banco' => $lp['nom_banco'],
            'cuenta' => $lp['nombre'],
            'numero' => $lp['numero'],
            'inicial' => $lp['inicial'] . ' - ' . $lp['final'],
            'en_uso' => $lp['en_uso'],
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar  . $acciones . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
