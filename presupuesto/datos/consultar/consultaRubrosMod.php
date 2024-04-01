<?php

include '../../../conexion.php';

$search = isset($_POST['search']) ? $_POST['search'] : exit('Acceso denegado');
$id_pto = $_POST['id_pto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_cargue`, `cod_pptal`, `nom_rubro`, `tipo_dato`
            FROM
                `pto_cargue`
            WHERE (`cod_pptal` LIKE '$search%' OR `nom_rubro` LIKE '$search%') AND `id_pto` = $id_pto";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = [];
if (!empty($datos)) {
    foreach ($datos as $row) {
        $response[] = array("value" => $row['id_cargue'], "label" => $row['cod_pptal'] . " - " . $row['nom_rubro'], "tipo" => $row['tipo_dato']);
    }
} else {
    $response[] = array("value" => "0", "label" => "No encontrado...", "tipo" => "3");
}
echo json_encode($response);
