<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$datos = isset($_POST['id']) ?  explode('|', $_POST['id']) : exit('Acción no permitida');
$id_novedad = $datos[0];
$novedad = $datos[1];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    switch ($novedad) {
        case 1:
        case 2:
        case 3:
            $sql = "DELETE FROM `ctt_novedad_adicion_prorroga` WHERE `id_nov_con` = ?";
            break;
        case 4:
            $sql = "DELETE FROM `ctt_novedad_cesion` WHERE `id_cesion` = ?";
            break;
        case 5:
            $sql = "DELETE FROM `ctt_novedad_suspension` WHERE `id_suspension` = ?";
            break;
        case 6:
            $sql = "DELETE FROM `ctt_novedad_reinicio` WHERE `id_reinicio` = ?";
            break;
        case 7:
            $sql = "DELETE FROM `ctt_novedad_terminacion` WHERE `id_terminacion` = ?";
            break;
        case 8:
            $sql = "DELETE FROM `ctt_novedad_liquidacion` WHERE `id_liquidacion` = ?";
            break;
    }
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_novedad, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
/*
//API
$url = $api . 'terceros/datos/res/eliminar/novedad/' . $datos;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$res =  json_decode($result, true);
echo $res;
*/