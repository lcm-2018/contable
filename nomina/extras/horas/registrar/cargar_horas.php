<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../simpleXLSX.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_empleado, no_documento FROM nom_empleado";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$file_tmp = $_FILES['fileDocHoEx']['tmp_name'];

move_uploaded_file($file_tmp, "horas_extra.xlsx");
$t = 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if (!empty($empleados)) {
    if (file_exists('horas_extra.xlsx')) {
        $xlsx = new SimpleXLSX('horas_extra.xlsx');
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_horas_ex_trab (id_empleado, id_he, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, fec_reg, tipo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idEmpHe, PDO::PARAM_INT);
            $sql->bindParam(2, $idHE, PDO::PARAM_INT);
            $sql->bindParam(3, $fihe, PDO::PARAM_STR);
            $sql->bindParam(4, $ffhe, PDO::PARAM_STR);
            $sql->bindParam(5, $hihe, PDO::PARAM_STR);
            $sql->bindParam(6, $hfhe, PDO::PARAM_STR);
            $sql->bindParam(7, $cantHE, PDO::PARAM_STR);
            $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(9, $tipo, PDO::PARAM_STR);

            foreach ($xlsx->rows() as $fila => $campo) {

                //Evitamos la primera columna, ya que tendrán las cabeceras.
                if ($fila < 1) {
                    continue;
                }

                $cedula = $campo[0];
                $tipo = $campo[12];
                $key = array_search($cedula, array_column($empleados, 'no_documento'));
                if (false !== $key) {
                    $idEmpHe = $empleados[$key]['id_empleado'];
                    $fihe = date('Y-m-d', strtotime($campo[1]));
                    $ffhe = date('Y-m-d', strtotime($campo[2]));
                    $hihe = date('h:i:s', strtotime($campo[3]));
                    $hfhe = date('h:i:s', strtotime($campo[4]));
                    $cantHE = $campo[5];
                    if ($cantHE > 0) {
                        $idHE = 1;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[6];
                    if ($cantHE > 0) {
                        $idHE = 2;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[7];
                    if ($cantHE > 0) {
                        $idHE = 3;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[8];
                    if ($cantHE > 0) {
                        $idHE = 4;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[9];
                    if ($cantHE > 0) {
                        $idHE = 5;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[10];
                    if ($cantHE > 0) {
                        $idHE = 6;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $cantHE = $campo[11];
                    if ($cantHE > 0) {
                        $idHE = 7;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2];
                        }
                    }
                    $t++;
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        if ($t > 0) {
            unlink('horas_extra.xlsx');
            echo '1';
        } else {
            unlink('horas_extra.xlsx');
            echo 'No se registró ninguna hora extra';
        }
    } else {
        echo "Archivo no encontrado";
    }
} else {
    echo 'No se econtró ningún empleado';
}
