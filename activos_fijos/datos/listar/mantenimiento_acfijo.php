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
                `id_mmto`, `num_orden`, `fec_inicia`, `fec_termina`, `tipo`, `concpeto`, `val_deterioro`, `observaciones`,`estado`
            FROM
                `seg_mantenimiento_acfijo`
            WHERE `id_serial` = '$id_ser'";
    $rs = $cmd->query($sql);
    $mantenimiento = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($mantenimiento)) {
    foreach ($mantenimiento as $mto) {
        $id_mmto = $mto['id_mmto'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_mmto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_mmto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar activo fijo"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ($mto['estado'] != 0) {
            $editar = $borrar = null;
            $hvida = '<a value="' . $id_mmto . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb vhvida" title="Ver Hoja de Vida"><span class="fas fa-file-alt fa-lg"></span></a>';
        } else {
            $hvida = '<a value="' . $id_mmto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb dhvida" title="Diligenciar Hoja de Vida"><span class="fab fa-wpforms fa-lg"></span></a>';
        }
        $data[] = [
            "id_mmto" => $id_mmto,
            "orden" => $mto['num_orden'],
            "fec_ini" => $mto['fec_inicia'],
            "fec_end" => $mto['fec_termina'],
            "tipo" => $mto['tipo'] == '1' ? 'PREVENTIVO' : 'CORRECTIVO',
            "concepto" => $mto['concpeto'],
            "deterioro" => '<div class="text-right">' . pesos($mto['val_deterioro']) . '</div>',
            "observaciones" => $mto['observaciones'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . $hvida . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
