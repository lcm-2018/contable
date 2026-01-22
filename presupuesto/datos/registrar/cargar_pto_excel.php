<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../simpleXLSX.php';

$id_pto = isset($_POST['idPto']) ? $_POST['idPto'] : exit('Acción no permitida');
$file_tmp = $_FILES['file']['tmp_name'];
$id_user = $_SESSION['id_user'];
move_uploaded_file($file_tmp, "file.xlsx");
$t = 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if (file_exists('file.xlsx')) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "DELETE FROM `pto_cargue`  WHERE `id_pto`  = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
        $sql->execute();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $xlsx = new SimpleXLSX('file.xlsx');
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "INSERT INTO `pto_cargue`
                    (`id_pto`,`id_tipo_recurso`,`cod_pptal`,`nom_rubro`,`tipo_dato`,`valor_aprobado`,`tipo_pto`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
        $sql->bindParam(2, $id_tipo_recurso, PDO::PARAM_INT);
        $sql->bindParam(3, $cod_pptal, PDO::PARAM_STR);
        $sql->bindParam(4, $nom_rubro, PDO::PARAM_STR);
        $sql->bindParam(5, $tipo_dato, PDO::PARAM_INT);
        $sql->bindParam(6, $valor_aprobado, PDO::PARAM_STR);
        $sql->bindParam(7, $tipo_pto, PDO::PARAM_INT);
        $sql->bindParam(8, $id_user, PDO::PARAM_INT);
        $sql->bindValue(9, $date->format('Y-m-d H:i:s'));

        foreach ($xlsx->rows() as $fila => $campo) {

            //Evitamos la primera columna, ya que tendrán las cabeceras.
            if ($fila < 1) {
                continue;
            }
            $id_tipo_recurso = $campo[0];
            $cod_pptal = $campo[1];
            $nom_rubro = $campo[2];
            $tipo_dato = $campo[3];
            $valor_aprobado = $campo[4];
            $tipo_pto = $campo[5];
            if ($tipo_dato == '0') {
                $nom_rubro = strtoupper($nom_rubro);
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
    if ($t > 0) {
        unlink('file.xlsx');
        echo 'ok';
    } else {
        unlink('file.xlsx');
        echo 'No se registró ningún registro';
    }
} else {
    echo "Archivo no encontrado";
}
