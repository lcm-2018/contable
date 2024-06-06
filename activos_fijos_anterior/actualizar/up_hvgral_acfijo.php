<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$lote = isset($_POST['txtLoteAF']) ? $_POST['txtLoteAF'] : exit('Acción no permitida');
$estado = $_POST['valEstado'];
$id_serie = $_POST['id_serial_hv'];
$fabricacion = $_POST['fecFabricacion'];
$invima = $_POST['txtRegINVIMA'];
$fabricante = $_POST['txtFabricante'];
$origen = $_POST['txtOrigen'];
$repre = $_POST['txtRepre'];
$dirrepre = $_POST['txtDirRepre'];
$telrepre = $_POST['txtTelRepre'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$ruta_del = $_POST['ruta_del'];
if ($estado == '0') {
    $nom_img = 'AF' . '_' . date('YmdGis') . '_' . $_FILES['upImageAF']['name'];
    $nom_img = strlen($nom_img) >= 101 ? substr($nom_img, 0, 100) : $nom_img;
    $temporal = $_FILES['upImageAF']['tmp_name'];
    $ruta = '../../images/activos_fijos/';
    if (!file_exists($ruta)) {
        $ruta = mkdir($ruta, 0777, true);
        $ruta = '../../images/activos_fijos/';
    }
    if (move_uploaded_file($temporal, $ruta . $nom_img)) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_hv_equipo`
                        (`id_serie`, `lote`, `anio_fab`, `reg_invima`, `fabricante`, `origen`, `representante`, `direccion`, `telefono`, `url_img`, `nomb_img`, `id_user_reg`, `fec_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
            $sql->bindParam(2, $lote, PDO::PARAM_STR);
            $sql->bindParam(3, $fabricacion, PDO::PARAM_STR);
            $sql->bindParam(4, $invima, PDO::PARAM_STR);
            $sql->bindParam(5, $fabricante, PDO::PARAM_STR);
            $sql->bindParam(6, $origen, PDO::PARAM_STR);
            $sql->bindParam(7, $repre, PDO::PARAM_STR);
            $sql->bindParam(8, $dirrepre, PDO::PARAM_STR);
            $sql->bindParam(9, $telrepre, PDO::PARAM_STR);
            $sql->bindParam(10, $ruta, PDO::PARAM_STR);
            $sql->bindParam(11, $nom_img, PDO::PARAM_STR);
            $sql->bindParam(12, $iduser, PDO::PARAM_INT);
            $sql->bindValue(13, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        echo 'No se pudo adjuntar el archivo';
        exit();
    }
} else {
    if (!isset($_FILES['upImageAF'])) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_hv_equipo`
                    SET `lote` = ?, `anio_fab` = ?, `reg_invima` = ?, `fabricante` = ?, `origen` = ?, `representante` = ?, `direccion` = ?, `telefono` = ?, `id_user_act` = ?, `fec_act` = ?
                    WHERE `id_serie` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $lote, PDO::PARAM_STR);
            $sql->bindParam(2, $fabricacion, PDO::PARAM_STR);
            $sql->bindParam(3, $invima, PDO::PARAM_STR);
            $sql->bindParam(4, $fabricante, PDO::PARAM_STR);
            $sql->bindParam(5, $origen, PDO::PARAM_STR);
            $sql->bindParam(6, $repre, PDO::PARAM_STR);
            $sql->bindParam(7, $dirrepre, PDO::PARAM_STR);
            $sql->bindParam(8, $telrepre, PDO::PARAM_STR);
            $sql->bindParam(9, $iduser, PDO::PARAM_INT);
            $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(11, $id_serie, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        $nom_img = 'AF' . '_' . date('YmdGis') . '_' . $_FILES['upImageAF']['name'];
        $nom_img = strlen($nom_img) >= 101 ? substr($nom_img, 0, 100) : $nom_img;
        $temporal = $_FILES['upImageAF']['tmp_name'];
        $ruta = '../../images/activos_fijos/';
        if (!file_exists($ruta)) {
            $ruta = mkdir($ruta, 0777, true);
            $ruta = '../../images/activos_fijos/';
        }
        if (move_uploaded_file($temporal, $ruta . $nom_img)) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_hv_equipo`
                            SET `lote` = ?, `anio_fab` = ?, `reg_invima` = ?, `fabricante` = ?, `origen` = ?, `representante` = ?, `direccion` = ?, `telefono` = ?, `url_img` = ?, `nomb_img` = ?
                        WHERE `id_serie` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $lote, PDO::PARAM_STR);
                $sql->bindParam(2, $fabricacion, PDO::PARAM_STR);
                $sql->bindParam(3, $invima, PDO::PARAM_STR);
                $sql->bindParam(4, $fabricante, PDO::PARAM_STR);
                $sql->bindParam(5, $origen, PDO::PARAM_STR);
                $sql->bindParam(6, $repre, PDO::PARAM_STR);
                $sql->bindParam(7, $dirrepre, PDO::PARAM_STR);
                $sql->bindParam(8, $telrepre, PDO::PARAM_STR);
                $sql->bindParam(9, $ruta, PDO::PARAM_STR);
                $sql->bindParam(10, $nom_img, PDO::PARAM_STR);
                $sql->bindParam(11, $id_serie, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    unlink($ruta_del);
                    echo '1';
                } else {
                    echo $sql->errorInfo()[2];
                }
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        } else {
            echo 'No se pudo adjuntar el archivo';
            exit();
        }
    }
}
