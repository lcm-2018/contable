<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';

$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$tipohe = array("Do", "No", "No", "Dd",  "Dd", "Nd", "Hd");
$idEmpHe = isset($_POST['numidemp']) ? $_POST['numidemp'] : exit('Acción no permitida');
$t = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_horas_ex_trab (id_empleado, id_he, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idEmpHe, PDO::PARAM_INT);
    $sql->bindParam(2, $idHE, PDO::PARAM_INT);
    $sql->bindParam(3, $fihe, PDO::PARAM_STR);
    $sql->bindParam(4, $ffhe, PDO::PARAM_STR);
    $sql->bindParam(5, $hihe, PDO::PARAM_STR);
    $sql->bindParam(6, $hfhe, PDO::PARAM_STR);
    $sql->bindParam(7, $cantHE, PDO::PARAM_STR);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));

    for ($i = 0; $i < 7; $i++) {
        if ($i === 2 || $i === 4) {
            $rc = 'Rec';
        } else {
            $rc = '';
        }
        $idHE = $_POST['num' . $rc . 'IdHe' . $tipohe[$i]];
        $fihe = date('Y-m-d', strtotime($_POST['dat' . $rc . 'FecLabHe' . $tipohe[$i] . 'I']));
        $ffhe = date('Y-m-d', strtotime($_POST['dat' . $rc . 'FecLabHe' . $tipohe[$i] . 'F']));
        $hihe = $_POST['time' . $rc . 'InicioHe' . $tipohe[$i]];
        $hfhe = $_POST['time' . $rc . 'FinHe' . $tipohe[$i]];
        $cantHE = $_POST['num' . $rc . 'CantHe' . $tipohe[$i]];
        if ($cantHE !== '99') {
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2];
            } else {
                $t++;
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($t > 0) {
    echo '1';
} else {
    echo 'No se registró ninguna hora extra';
}
