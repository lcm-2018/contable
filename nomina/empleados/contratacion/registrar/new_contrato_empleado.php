<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
$idemple = isset($_POST['slcEmpleado']) ? $_POST['slcEmpleado'] : exit('Acción no permitida');
$inicio = date('Y-m-d', strtotime($_POST['datFecInicio']));
$fin = date('Y-m-d', strtotime($_POST['datFecFin']));
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_contratos_empleados 
            WHERE estado = '0'
            ORDER BY fec_fin DESC";
    $rs = $cmd->query($sql);
    $contratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search($idemple, array_column($contratos, 'id_empleado'));
if (false !== $key) {
    $ftermina = $contratos[$key]['fec_fin'];
    if ($ftermina >= $inicio) {
        echo 'Empleado tiene contrato vigente hasta el ' . $ftermina;
    } else {
        newContratoEmpleado();
    }
} else {
    newContratoEmpleado();
}

function newContratoEmpleado()
{
    include '../../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO nom_contratos_empleados (id_empleado, fec_inicio, fec_fin, vigencia, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $GLOBALS['idemple'], PDO::PARAM_INT);
        $sql->bindParam(2, $GLOBALS['inicio'], PDO::PARAM_STR);
        $sql->bindParam(3, $GLOBALS['fin'], PDO::PARAM_STR);
        $sql->bindParam(4, $GLOBALS['vigencia'], PDO::PARAM_STR);
        $sql->bindParam(5, $GLOBALS['iduser'], PDO::PARAM_INT);
        $sql->bindValue(6, $GLOBALS['date']->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}