<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../simpleXLSX.php';

$file_tmp = $_FILES['file']['tmp_name'];
$id_user = $_SESSION['id_user'];
$estado = 1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT COUNT(*) AS `cantiad` FROM `ctb_libaux`";
    $rs = $cmd->query($sql);
    $registros = $rs->fetch();
    $registros = $registros['cantiad'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    echo 'No se puede cargar el archivo, ya que existen registros en libros auxiliares';
    exit();
} else {
    move_uploaded_file($file_tmp, "file.xlsx");
    $t = 0;
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    if (file_exists('file.xlsx')) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "DELETE FROM `ctb_pgcp` WHERE `id_pgcp` >= ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->execute();
            $sql = "ALTER TABLE `ctb_pgcp` AUTO_INCREMENT = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->execute();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $xlsx = new SimpleXLSX('file.xlsx');
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "INSERT INTO `ctb_pgcp`
                        (`fecha`,`cuenta`,`nombre`,`tipo_dato`,`estado`,`id_user_reg`,`fec_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $cuenta, PDO::PARAM_STR);
            $sql->bindParam(3, $nombre, PDO::PARAM_STR);
            $sql->bindParam(4, $tipo_dato, PDO::PARAM_STR);
            $sql->bindParam(5, $estado, PDO::PARAM_INT);
            $sql->bindParam(6, $id_user, PDO::PARAM_INT);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
            foreach ($xlsx->rows() as $fila => $campo) {

                //Evitamos la primera columna, ya que tendrán las cabeceras.
                if ($fila < 1) {
                    continue;
                }
                $cuenta = $campo[0];
                $nombre = $campo[1];
                $tipo_dato = $campo[2];
                if ($tipo_dato == '0') {
                    $nombre = strtoupper($nombre);
                    $tipo_dato = 'M';
                } else {
                    $tipo_dato = 'D';
                }
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2];
                } else {
                    $t++;
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        unlink('file.xlsx');
        if ($t > 0) {
            echo 'ok';
        } else {
            echo 'No se registró ningún registro';
        }
    } else {
        echo "Archivo no encontrado";
    }
}
unlink('file.xlsx');
