<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

$cc = isset($_POST['txtDocEmpViat']) ? $_POST['txtDocEmpViat'] : exit('Acción no permitida');
$dgen = $_POST['txtDescViat'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_empleado FROM nom_empleado WHERE no_documento = '$cc'";
    $rs = $cmd->query($sql);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $obj = $rs->fetch();
    $idEmpViat = $obj['id_empleado'];
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_viaticos (desc_general, id_emplead) VALUES (?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $dgen, PDO::PARAM_STR);
    $sql->bindParam(2, $idEmpViat, PDO::PARAM_INT);
    $sql->execute();
    $idviaticos = $cmd->lastInsertId();
    if ($idviaticos > 0) {
        for ($j = 0; $j < 20; $j++) {
            $res = 0;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO seg_detalle_viaticos (id_viaticos, concepto, valor, fviatico,fec_reg) VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idviaticos, PDO::PARAM_INT);
            $sql->bindParam(2, $concepto, PDO::PARAM_STR);
            $sql->bindParam(3, $valor, PDO::PARAM_INT);
            $sql->bindParam(4, $fviat);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            if (isset($_POST['txtConcepViat' . $j])) {
                $concepto = $_POST['txtConcepViat' . $j];
                if (isset($_POST['checkP' . $j])) {
                    $valor = $_POST['numValViat' . $j];
                } else {
                    $valor = $_POST['numValViat' . $j] * 0.5;
                }
                $fviat = date('Y-m-d', strtotime($_POST['datFecViat' . $j]));
                $sql->execute();
                $res = $cmd->lastInsertId();
                if ($res > 0) {
                    $add = 1;
                } else {
                    echo $sql->errorInfo()[2];
                }
            }
        }
        if ($add > 0) {
            echo '1';
        } else {
            echo 'Operación fallida';
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
