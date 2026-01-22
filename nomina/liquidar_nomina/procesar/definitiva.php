<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_nomina = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $estado = $_SESSION['pto'] == '1' ? 2 : 3;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "UPDATE `nom_nominas` SET `estado` = ?, `planilla` = ? WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $estado, PDO::PARAM_STR);
    $sql->bindParam(3, $id_nomina, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        if ($estado == 3) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $sq = "SELECT `tipo` FROM `nom_nominas` WHERE `id_nomina` = $id_nomina";
                $rs = $cmd->query($sq);
                $data = $rs->fetch();
                $tipo = $data['tipo'];

                $query = "INSERT INTO `nom_nomina_pto_ctb_tes` (`id_nomina`, `cdp`, `crp`, `tipo`) 
                            VALUES (?, ?, ?, ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $id_nomina, PDO::PARAM_INT);
                $query->bindParam(2, $id_nomina, PDO::PARAM_INT);
                $query->bindParam(3, $id_nomina, PDO::PARAM_INT);
                $query->bindParam(4, $tipo, PDO::PARAM_STR);
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                } else {
                    if ($tipo == 'N' || $tipo == 'VC' || $tipo == 'RA' || $tipo == 'PS') {
                        $tipo = 'PL';
                        $query->execute();
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        echo 'ok';
    } else {
        echo 'error ' . $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
