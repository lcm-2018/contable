<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$id_vigencia = $_SESSION['id_vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_causacion`.`id_causacion`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre` AS `nom_cta`
                , `tb_centrocostos`.`nom_centro` AS `centro_costo`
                , `nom_causacion`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
            FROM
                `nom_causacion`
                LEFT JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`nom_causacion`.`cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `tb_centrocostos` 
                    ON (`tb_centrocostos`.`id_centro` = `nom_causacion`.`centro_costo`)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($cuentas as $tn) {
    $id = $tn['id_causacion'];
    $editar = $eliminar = NULL;
    if (PermisosUsuario($permisos, 5114, 3) || $id_rol == 1) {
        $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar Rubros"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    }
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar Rubros"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $datos[] = [
        'id_causacion' => $id,
        'ccosto' => $tn['centro_costo'],
        'tipo' => $tn['id_tipo'],
        'nom_tipo' => $tn['nombre'],
        'cuenta' => $tn['cuenta'],
        'nom_cta' => $tn['nom_cta'],
        'acciones' => '<div class="text-center">' . $editar . $eliminar . '</div>'
    ];
}
$data = [
    'data' => $datos
];
echo json_encode($data);
