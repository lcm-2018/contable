<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sq2 = "SELECT
        `ctb_libaux`.`id_ctb_libaux`
        , `ctb_pgcp`.`cuenta`
        , `ctb_pgcp`.`nombre`
        , `ctb_libaux`.`debito`
        , `ctb_libaux`.`credito`
        FROM
        `ctb_libaux`
        INNER JOIN `ctb_pgcp` 
            ON (`ctb_libaux`.`cuenta` = `ctb_pgcp`.`cuenta`)
        WHERE (`ctb_libaux`.`id_ctb_libaux` =$id);";
    $rs = $pdo->query($sq2);
    $resultado = $rs->fetch();
    $cuenta = $resultado['cuenta'];
    $nombre = $resultado['nombre'];
    $debito = $resultado['debito'];
    $credito = $resultado['credito'];
    // si $resultado es vacio se consulta solo valores
    if ($resultado == null) {
        $sq2 = "SELECT debito, credito FROM `ctb_libaux` WHERE `id_ctb_libaux` =$id;";
        $rs = $pdo->query($sq2);
        $resultado = $rs->fetch();
        $cuenta = '';
        $nombre = '';
        $debito = $resultado['debito'];
        $credito = $resultado['credito'];
    }
    $response[] = array("value" => 'ok', "cuenta" => $cuenta, "nombre" => $nombre, "debito" => $debito, "credito" => $credito);
    echo json_encode($response);
} catch (PDOException $e) {
}
