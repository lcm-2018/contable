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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_notas_acfijo`.`id_nota`
                , `seg_tipo_notas_acfijo`.`descripcion`
                , `seg_notas_acfijo`.`fecha_n`
                , `seg_notas_acfijo`.`valor`
                , `seg_notas_acfijo`.`observacion`
            FROM
                `seg_notas_acfijo`
                INNER JOIN `seg_tipo_notas_acfijo` 
                    ON (`seg_notas_acfijo`.`id_tipo_n` = `seg_tipo_notas_acfijo`.`id_tipo_nota`)
            WHERE `seg_notas_acfijo`.`id_serie` = '$id_ser'";
    $rs = $cmd->query($sql);
    $notas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($notas)) {
    foreach ($notas as $nt) {
        $id_nota = $nt['id_nota'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_nota . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_nota . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar activo fijo"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_nota" => $id_nota,
            "descripcion" => $nt['descripcion'],
            "fecha_n" => $nt['fecha_n'],
            "valor" => '<div class="text-right">' . pesos($nt['valor']) . '</div>',
            "observacion" => $nt['observacion'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
