<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_sede = isset($_POST['id']) ? $_POST['id'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_centro_costo_x_sede`.`id_x_sede`
                , `tb_centros_costo`.`descripcion`
            FROM
                `tb_centro_costo_x_sede`
                INNER JOIN `tb_sedes` 
                    ON (`tb_centro_costo_x_sede`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `tb_centros_costo` 
                    ON (`tb_centro_costo_x_sede`.`id_centro_c` = `tb_centros_costo`.`id_centro`)
            WHERE `tb_centro_costo_x_sede`.`id_sede` = '$id_sede'";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$res = '';
if (!empty($centros)) {
    $res .= '<option value="0">--Seleccione--</option>';
    foreach ($centros as $cc) {
        $res .= '<option value="' . $cc['id_x_sede'] . '">' . $cc['descripcion'] . '</option>';
    }
} else {
    $res .= '<option value="0">No se econtraron centros de costo en esta sede</option>';
}
echo $res;
