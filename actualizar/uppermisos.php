<?php

session_start();
include '../conexion.php';
$idper = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$tipoperm = $_POST['perm'];
$estad = $_POST['est'] == '1' ? '0' : '1';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
switch ($tipoperm) {
    case 'L':
        $tipo = 'listar';
        break;
    case 'R':
        $tipo = 'registrar';
        break;
    case 'E':
        $tipo = 'editar';
        break;
    case 'B':
        $tipo = 'borrar';
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_permisos_usuario SET " . $tipo . "= ?, fec_act = ? WHERE id_permiso = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estad);
    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(3, $idper);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        print_r($cmd->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
