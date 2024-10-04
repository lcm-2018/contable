<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
            nom_vacaciones
            WHERE id_empleado ='$id'";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
$data = [];
if (!empty($vacaciones)) {
    foreach ($vacaciones as $v) {
        $idVac = $v['id_vac'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idVac . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idVac . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($v['estado'] != '1') {
            $borrar = $editar = null;
        }
        $imprimir = '<button value="' . $idVac . '" class="btn btn-outline-success btn-sm btn-circle imprimir" title="Imprimir"><span class="fas fa-print fa-lg"></span></button>';
        $anticipo = $v['anticipo'] == '1' ? '<span class="badge badge-success">SI</span>' : '<span class="badge badge-secondary">NO</span>';
        $data[] = [
            'id_vac' => $idVac,
            'anticipada' => '<div class="text-center">' . $anticipo . '</div>',
            'fec_inicio' => $v['fec_inicial'],
            'fec_fin' => $v['fec_fin'],
            'dias_inactivo' => $v['dias_inactivo'],
            'dias_hab' => $v['dias_habiles'],
            'corte' => $v['corte'],
            'dias_liq' => $v['dias_liquidar'],
            'botones' => '<div class="center-block">' . $editar . $borrar . $imprimir . '</div>'
        ];
    }
}

$datos = ['data' => $data];
echo json_encode($datos);
