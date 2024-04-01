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
                `nom_resolucion_viaticos`.`id_resol_viat`
                , `nom_resolucion_viaticos`.`id_cdp`
                , `nom_resolucion_viaticos`.`id_empleado`
                , `nom_resolucion_viaticos`.`no_resolucion`
                , `nom_resolucion_viaticos`.`fec_inicia`
                , `nom_resolucion_viaticos`.`fec_final`
                , `nom_resolucion_viaticos`.`tot_dias`
                , `nom_resolucion_viaticos`.`dias_pernocta`
                , `nom_resolucion_viaticos`.`objetivo`
                , `nom_resolucion_viaticos`.`destino`
                , `nom_resolucion_viaticos`.`grupo`
                , `nom_resolucion_viaticos`.`vigencia`
                , `nom_empleado`.`no_documento`
                , CONCAT_WS(' ', `nom_empleado`.`apellido1`, `nom_empleado`.`apellido2`, `nom_empleado`.`nombre2`, `nom_empleado`.`nombre1`) AS `nombre_completo`
            FROM
                `nom_resolucion_viaticos`
                INNER JOIN `nom_empleado` 
                    ON (`nom_resolucion_viaticos`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE `nom_resolucion_viaticos`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $resoluciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($resoluciones as $resol) {
    $id_resol = $resol['id_resol_viat'];
    $editar = $borrar = null;
    if (PermisosUsuario($permisos, 5103, 3) || $id_rol == 1) {
        $editar = '<a value="' . $id_resol . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    }
    if (PermisosUsuario($permisos, 5103, 4) || $id_rol == 1) {
        $borrar = '<a value="' . $id_resol . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Borrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $descargar = '<a value="' . $id_resol . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb descargar" title="Descargar Word"><span class="fas fa-file-word fa-lg"></span></a>';
    $datos[] = array(
        'grupo' => $resol['grupo'],
        'no_resolucion' => str_pad($resol['no_resolucion'], 5, '0', STR_PAD_LEFT),
        'id_cdp' => $resol['id_cdp'],
        'no_documento' => $resol['no_documento'],
        'nombre' => mb_strtoupper($resol['nombre_completo']),
        'fec_inicia' => $resol['fec_inicia'],
        'fec_final' => $resol['fec_final'],
        'tot_dias' => $resol['tot_dias'],
        'dias_pernocta' => $resol['dias_pernocta'],
        'objetivo' => $resol['objetivo'],
        'destino' => $resol['destino'],
        'botones' => '<div class="text-center">' . $editar . $borrar . $descargar . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
