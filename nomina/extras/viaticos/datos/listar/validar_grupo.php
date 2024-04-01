<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$num_grupo = isset($_POST['grupo']) ? $_POST['grupo'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `grupo`
            FROM
                `nom_resolucion_viaticos`
            WHERE `grupo` = '$num_grupo' AND `vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $grupo = $rs->fetchAll();
    if (count($grupo) > 0) {
        echo '1';
    } else {
        echo 'El grupo ingresado no exite o no esta disponible en esta vigencia';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
