<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
include '../../../../permisos.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `nom_contratos_empleados`.`fec_inicio`
                , `nom_contratos_empleados`.`fec_fin`
                , `nom_salarios_basico`.`salario_basico`
                , `nom_contratos_empleados`.`id_contrato_emp`
                , `nom_contratos_empleados`.`estado`
            FROM
                `nom_contratos_empleados`
                LEFT JOIN `nom_salarios_basico` 
                    ON (`nom_contratos_empleados`.`id_salario` = `nom_salarios_basico`.`id_salario`)
            WHERE (`nom_contratos_empleados`.`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $contratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];

if (!empty($contratos)) {
    foreach ($contratos as $a) {
        $borrar = $editar = null;
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $a['id_contrato_emp'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        }

        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $a['id_contrato_emp'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        }

        if ($a['estado'] == 1) {
            $estado = '<span class="badge badge-success">Activo</span>';
        } else {
            $estado = '<span class="badge badge-danger">Inactivo</span>';
        }
        $data[] = [
            'id' => $a['id_contrato_emp'],
            'inicia' => $a['fec_inicio'],
            'ternina' => $a['fec_fin'],
            'salario' => '<div class="text-right">' . $a['salario_basico'] . '</div>',
            'estado' =>  '<div class="text-center">' .$estado . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
