<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_pto_crp = $_POST['id_crp'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_mvto,id_pto_doc,rubro,valor FROM pto_documento_detalles WHERE id_pto_doc=$id_pto_crp AND tipo_mov='CRP'";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        //Consulto el nombre del rubro
        $sql = "SELECT nom_rubro FROM pto_cargue WHERE cod_pptal='$lp[rubro]'";
        $rs = $cmd->query($sql);
        $nomrubro = $rs->fetch();
        $nombre = $nomrubro['nom_rubro'];

        $id_pto = $lp['id_pto_doc'];
        $id = $lp['id_pto_mvto'];
        // Valor con separador de mailes
        $valor = number_format($lp['valor'], 2, '.', ',');
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" onclick=Editar("' . $id . '") class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id . '" onclick=Eliminar("' . $id . '") class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [

            'rubro' => $lp['rubro'] . ' - ' . $nombre,
            'valor' => '<div class="text-right">' . $valor . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar .  '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
