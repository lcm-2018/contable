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
                `nom_rel_rubro`.`id_relacion`
                , `nom_rel_rubro`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_rel_rubro`.`r_admin`
                , `pto_admin`.`cod_pptal` AS `cod_admin`
                , `pto_admin`.`nom_rubro` AS `nom_admin`
                , `nom_rel_rubro`.`r_operativo`
                , `pto_operativo`.`cod_pptal` AS `cod_opera`
                , `pto_operativo`.`nom_rubro` AS `nom_opera`
                , `nom_rel_rubro`.`id_vigencia`
            FROM
                `nom_rel_rubro`
                INNER JOIN `pto_cargue` AS `pto_operativo` 
                    ON (`nom_rel_rubro`.`r_operativo` = `pto_operativo`.`id_cargue`)
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_rel_rubro`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
                INNER JOIN `pto_cargue` AS `pto_admin`
                    ON (`nom_rel_rubro`.`r_admin` = `pto_admin`.`id_cargue`)
            WHERE (`nom_rel_rubro`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($rubros as $tn) {
    $id = $tn['id_relacion'];
    $editar = $eliminar = NULL;
    if (PermisosUsuario($permisos, 5114, 3) || $id_rol == 1) {
        $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar Rubros"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    }
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar Rubros"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $datos[] = [
        'id_relacion' => $id,
        'nombre' => $tn['nombre'],
        'cod_admin' => $tn['cod_admin'],
        'nom_admin' => $tn['nom_admin'],
        'cod_opera' => $tn['cod_opera'],
        'nom_opera' => $tn['nom_opera'],
        'acciones' => '<div class="text-center">' . $editar . $eliminar . '</div>'
    ];
}
$data = [
    'data' => $datos
];
echo json_encode($data);
