<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
include '../../../../permisos.php';

$anulados = isset($_POST['anulados']) ? $_POST['anulados'] : 0;
if ($anulados == 0) {
    $where = '> 0';
} else {
    $where = '>= 0';
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *  FROM nom_empleado WHERE estado $where";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$vigencia = $_SESSION['vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `nom_salarios_basico`.`id_empleado`
                , `nom_salarios_basico`.`id_salario`
                , `nom_salarios_basico`.`vigencia`
                , `nom_salarios_basico`.`salario_basico`
            FROM (SELECT
                MAX(`id_salario`) AS `id_salario`, `id_empleado`
                FROM
                    `nom_salarios_basico`
                WHERE `vigencia` <= '$vigencia'
                GROUP BY `id_empleado`) AS `t`
            INNER JOIN `nom_salarios_basico`
                ON (`nom_salarios_basico`.`id_salario` = `t`.`id_salario`)";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($obj)) {
    foreach ($obj as $o) {
        $idEmp = $o['id_empleado'];
        $editar = $borrar = $estado = $detalles = $horas = null;
        if ((PermisosUsuario($permisos, 5101, 2) || $id_rol == 1)) {
            $detalles = '<button value="' . $idEmp . '" class="btn btn-outline-warning btn-sm btn-circle detalles" title="Detalles"><span class="far fa-eye fa-lg"></span></button>';
            $horas = '<button value="' . $idEmp . '" class="btn btn-outline-success btn-sm btn-circle horas" title="Horas Extras"><span class="fas fa-clock fa-lg"></span></button>';
        }
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idEmp . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
            $estado = '<div class="text-center"><button value="' . $idEmp . '" class="btn-estado btn btn-outline-' . ($o['estado'] == '1' ? 'success' : 'secondary') . ' btn-sm btn-circle estado" title="' . ($o['estado'] == 1 ? 'Activo' : 'Inactivo') . '"><span class="fas fa-toggle-' . ($o['estado'] == 1 ? 'on' : 'off') . ' fa-lg"></span></button></div>';
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idEmp . '" class="btn btn-outline-danger btn-sm btn-circle eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        }

        if ($o['estado'] == 0) {
            $borrar = $editar = $horas = null;
        }
        $key = array_search($idEmp, array_column($salarios, 'id_empleado'));
        if (false !== $key) {
            $salario = $salarios[$key]['salario_basico'];
        } else {
            $salario = 0;
        }
        $data[] = [
            'id' => $o['id_empleado'],
            'doc' => '<div  class="text-right">' . $o['no_documento'] . '</div>',
            'nombre' => trim($o['nombre1'] . ' ' . $o['nombre2'] . ' ' . $o['apellido1'] . ' ' . $o['apellido2']),
            'correo' => $o['correo'],
            'tel' => $o['telefono'],
            'salario' => '<div  class="text-right">' . pesos($salario) . '</div>',
            'estado' => '<div class="text-center" id="tdEstado">' . $estado . '</div>',
            'opciones' => '<div class="text-center">' . $detalles . $editar . $horas . $borrar . '</div>',
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
