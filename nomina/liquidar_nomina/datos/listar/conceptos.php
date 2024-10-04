<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_vigencias`.`anio`
                , `nom_valxvigencia`.`id_concepto`
                , `nom_conceptosxvigencia`.`concepto`
                , `nom_valxvigencia`.`valor`
                , `nom_valxvigencia`.`id_valxvig`
            FROM
                `nom_valxvigencia`
                INNER JOIN `nom_conceptosxvigencia` 
                    ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE (`tb_vigencias`.`anio` = '$vigencia')";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($conceptos as $cp) {
    $id = $cp['id_valxvig'];
    if (PermisosUsuario($permisos, 5114, 3) || $id_rol == 1) {
        $actualizar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb actualizar" title="Actualizar valor concepto"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    }
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar concepto"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $datos[] = array(
        'id' => $cp['id_concepto'],
        'concepto' => mb_strtoupper($cp['concepto']),
        'valor' => '<div class="text-right">' . pesos($cp['valor']) . '</div>',
        'botones' => '<div class="text-center">' . $actualizar . $eliminar . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
