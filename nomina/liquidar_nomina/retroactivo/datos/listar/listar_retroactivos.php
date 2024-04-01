<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `nom_retroactivos`.`id_retroactivo`
            , `nom_retroactivos`.`fec_inicio`
            , `nom_retroactivos`.`fec_final`
            , `nom_retroactivos`.`meses`
            , `nom_retroactivos`.`id_incremento`
            , `nom_incremento_salario`.`porcentaje`
            , `nom_retroactivos`.`observaciones`
            , `nom_retroactivos`.`estado`
            , `nom_retroactivos`.`vigencia`
        FROM
            `nom_retroactivos`
            INNER JOIN `nom_incremento_salario` 
                ON (`nom_retroactivos`.`id_incremento` = `nom_incremento_salario`.`id_inc`)
        WHERE (`nom_retroactivos`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $retroactivos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($retroactivos as $ra) {
    $id = $ra['id_retroactivo'];
    $editar = $borrar = $incrementa = null;
    if ($ra['estado'] == '1') {
        if (PermisosUsuario($permisos, 5105, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $incrementa = '<a value="' . $id . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb incrementar" title="Efectuar incremento"><span class="fas fa-sort-amount-up fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5105, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
    } else {
        $incrementa = '<a value="' . $id . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb incrementar" title="Efectuar incremento"><span class="fas fa-sort-amount-up fa-lg"></span></a>';
    }
    $datos[] = array(
        'id' => $id,
        'inicia' => $ra['fec_inicio'],
        'termina' => $ra['fec_final'],
        'meses' => $ra['meses'],
        'incremento' => $ra['porcentaje'].' %',
        'observa' => $ra['observaciones'],
        'botones' => '<div class="text-center">' . $editar . $borrar . $incrementa . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
