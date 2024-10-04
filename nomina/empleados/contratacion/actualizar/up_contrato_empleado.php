<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$idce = isset($_POST['id_ce']) ? $_POST['id_ce'] : exit('Acción no permitida');
$idemp = $_POST['id_emp'];
$fini = date('Y-m-d', strtotime($_POST['datFecInicio']));
if ($_POST['datFecFin'] === '') {
    $ffin;
} else {
    $ffin = date('Y-m-d', strtotime($_POST['datFecFin']));
}
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_contratos_empleados 
            WHERE estado = '0' AND id_contrato_emp <> '$idce'
            ORDER BY fec_fin DESC";
    $rs = $cmd->query($sql);
    $contratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search($idemp, array_column($contratos, 'id_empleado'));
if (false !== $key) {
    $ftermina = $contratos[$key]['fec_fin'];
    if ($ftermina >= $fini) {
        echo 'Empleado tiene contrato vigente hasta el ' . $ftermina;
    } else {
        upContratoEmpleado();
    }
} else {
    upContratoEmpleado();
}

function upContratoEmpleado()
{
    include '../../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE nom_contratos_empleados SET fec_inicio = ?, fec_fin = ? WHERE id_contrato_emp = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $GLOBALS['fini'], PDO::PARAM_STR);
        $sql->bindParam(2, $GLOBALS['ffin'], PDO::PARAM_STR);
        $sql->bindParam(3, $GLOBALS['idce'], PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $updata = 1;
        } else {
            $updata = 0;
        }
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        }
        if ($updata > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE nom_contratos_empleados SET  id_user_act = ?, fec_act = ? WHERE id_contrato_emp = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $GLOBALS['iduser'], PDO::PARAM_INT);
            $sql->bindValue(2, $GLOBALS['date']->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $GLOBALS['idce'], PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se ingresó ningún dato nuevo.';
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
