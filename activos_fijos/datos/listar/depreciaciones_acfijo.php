<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_ser = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Accion no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_depreciacion`.`id_depreciacion`
                , `seg_metodo_deprecia`.`descripcion`
                , `seg_depreciacion`.`fec_inicia`
                , `seg_depreciacion`.`vida_util`
                , `seg_depreciacion`.`valor_residual`
                , `seg_depreciacion`.`capacidad_produccion`
                , `seg_depreciacion`.`observacion`
            FROM
                `seg_depreciacion`
                INNER JOIN `seg_metodo_deprecia` 
                    ON (`seg_depreciacion`.`id_metodo` = `seg_metodo_deprecia`.`id_metodo`)
            WHERE `seg_depreciacion`.`id_num_serie` = '$id_ser'";
    $rs = $cmd->query($sql);
    $depreciacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($depreciacion)) {
    foreach ($depreciacion as $dp) {
        $id_de = $dp['id_depreciacion'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_de . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_de . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar activo fijo"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id" => $id_de,
            "tdeprecia" => $dp['descripcion'],
            "fecha" => $dp['fec_inicia'],
            "vida_util" => $dp['vida_util'],
            "val_resid" => '<div class="text-right">' . pesos($dp['valor_residual']) . '</div>',
            "capacidad" => $dp['capacidad_produccion'],
            "observa" => $dp['observacion'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
