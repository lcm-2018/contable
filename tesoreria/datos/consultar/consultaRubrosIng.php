<?php
session_start();
include '../../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$_post = json_decode(file_get_contents('php://input'), true);
$search = $_post['search'][0];
if (isset($_POST['search'])) {
    $sql = "SELECT
            `pto_cargue`.`cod_pptal`
            , `pto_cargue`.`nom_rubro`
            , `pto_cargue`.`tipo_dato`
        FROM
            `pto_presupuestos`
            INNER JOIN `pto_cargue` 
                ON (`pto_presupuestos`.`id_pto` = `pto_cargue`.`id_pto_presupuestos`)
        WHERE (`pto_cargue`.`cod_pptal` LIKE '$search%'
            AND `pto_cargue`.`id_pto_presupuestos` =1
            AND `pto_cargue`.`vigencia` ={$_SESSION['vigencia']});";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll();
    foreach ($datos as $key => $value) {
        $response[] = array("value" => $value['cod_pptal'], "label" => $value['cod_pptal'] . " - " . $value['nom_rubro'], "tipo" => $value['tipo_dato']);
    }
    echo json_encode($response);
}

exit;
