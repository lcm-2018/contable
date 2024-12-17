<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida .-');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_dcto`, `fecha`, `concepto`, `valor`, `descripcion` AS `tipo`, `estado`
            FROM
                `nom_otros_descuentos`
            INNER JOIN `nom_tipo_descuentos` 
                ON (`nom_otros_descuentos`.`id_tipo_dcto` = `nom_tipo_descuentos`.`id_tipo`)
            WHERE (`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
$data = [];
if (!empty($descuentos)) {
    foreach ($descuentos as $l) {
        $id_dcto = $l['id_dcto'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $id_dcto . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $id_dcto . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($l['estado'] == 0) {
            $borrar = $editar = null;
        }

        $estado = $l['estado'] == 1 ? '<span class="badge badge-success">Activo</span><button value="' . $l['id_dcto'] 
        . '" class="btn btn-outline-success btn-sm btn-circle estado" title="Cambiar Estado" estado="' . $l['estado'] 
        . '"><span class="fas fa-exchange-alt"></span></button>' : '<span class="badge badge-secondary">Inactivo</span><button value="' . $l['id_dcto'] 
        . '" class="btn btn-outline-secondary btn-sm btn-circle estado" title="Cambiar Estado"  estado="' . $l['estado'] 
        . '"><span class="fas fa-exchange-alt"></span></button>';
        $data[] = [
            'id_dcto' => $id_dcto,
            'fecha' => $l['fecha'],
            'tipo' => $l['tipo'],
            'concepto' => $l['concepto'],
            'valor' => '<div class="text-right">' . pesos($l['valor']) . '</div>',
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
