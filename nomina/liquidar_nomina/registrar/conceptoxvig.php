<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$concepto = $_POST['concepto'];
$valor = $_POST['valor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_valxvigencia`.`id_valxvig`
            FROM
                `nom_valxvigencia`
                INNER JOIN `nom_conceptosxvigencia` 
                    ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE (`tb_vigencias`.`anio` = '$vigencia' AND `nom_valxvigencia`.`id_concepto` = '$concepto')";
    $rs = $cmd->query($sql);
    $resultado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_vigencia`, `anio`
            FROM
                `tb_vigencias`
            WHERE (`anio` = '$vigencia') LIMIT 1";
    $rs = $cmd->query($sql);
    $idvig = $rs->fetch(PDO::FETCH_ASSOC);
    $id_vigencia = $idvig['id_vigencia'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($resultado)) {
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "INSERT INTO `nom_valxvigencia` (`id_vigencia`, `id_concepto`, `valor`, `fec_reg`)
                VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $sql->bindParam(2, $concepto, PDO::PARAM_INT);
        $sql->bindParam(3, $valor, PDO::PARAM_STR);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Concepto ya registrado para la vigencia ' . $vigencia;
    exit();
}
