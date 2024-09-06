<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_cargo_empleado`.`id_cargo`
                , `nom_cargo_empleado`.`codigo` AS `id_codigo`
                , `nom_cargo_empleado`.`descripcion_carg`
                , `nom_cargo_empleado`.`grado`
                , `nom_cargo_empleado`.`perfil_siho`
                , `nom_cargo_empleado`.`id_nombramiento`
                , `nom_cargo_codigo`.`codigo`
                , `nom_cargo_nombramiento`.`tipo`
            FROM
                `nom_cargo_empleado`
                LEFT JOIN `nom_cargo_codigo` 
                    ON (`nom_cargo_empleado`.`codigo` = `nom_cargo_codigo`.`id_cod`)
                LEFT JOIN `nom_cargo_nombramiento` 
                    ON (`nom_cargo_empleado`.`id_nombramiento` = `nom_cargo_nombramiento`.`id`)";
    $rs = $cmd->query($sql);
    $cargos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($cargos as $tn) {
    $id = $tn['id_cargo'];
    $editar = $eliminar = NULL;
    if (PermisosUsuario($permisos, 5114, 3) || $id_rol == 1) {
        $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar cargo"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    }
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar Carggo"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $datos[] = [
        'id_cargo' => $id,
        'codigo' => $tn['codigo'],
        'cargo' => $tn['descripcion_carg'],
        'grado' => $tn['grado'],
        'perfil_siho' => $tn['perfil_siho'],
        'nombramiento' => $tn['tipo'],
        'acciones' => '<div class="text-center">' . $editar . $eliminar . '</div>'
    ];
}
$data = [
    'data' => $datos
];
echo json_encode($data);
