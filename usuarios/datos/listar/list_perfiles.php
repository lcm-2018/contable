<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
include '../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_rol`,`nom_rol` FROM `seg_rol`";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($obj)) {
    foreach ($obj as $o) {
        $id = $o['id_rol'];
        if ($id != 1) {
            $editar = $borrar = $set = null;
            if ($id_rol == 1) {
                $editar = '<a  class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                $borrar = '<a class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                $set = '<a class="btn btn-outline-warning btn-sm btn-circle shadow-gb setPermisos" title="Configurar permisos"><span class="fas fa-user-cog fa-lg"></span></a>';
            }
            $data[] = [
                'id_rol' => $id,
                'rol' => mb_strtoupper($o['nom_rol']),
                'botones' => '<div class="text-center" text="' . $id . '">' . $editar . $borrar . $set . '</div>',
            ];
        }
    }
}

$datos = [
    'data' => $data,
];
echo json_encode($datos);
