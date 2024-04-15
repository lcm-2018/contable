<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
//Array ( [numDoc] => 2 [tipodato] => 18 [id_crpp] => 0 [fecha] => 2024-04-04 [tercero] => GOMEZ BARRERA EDWIN LEONARDO [id_tercero] => 2097 [objeto] => [detalle] => ooooo [id_ctb_doc] => 21 [tableMvtoContableDetalle_length] => 10 [codigoCta] => 11050201 - CAJA MENOR [id_codigoCta] => 7 [tipoDato] => D [bTercero] => GOMEZ BARRERA EDWIN LEONARDO || 1057608892 [idTercero] => 2097 [valorDebito] => 2,000,000.00 [valorCredito] => 0 );
include '../../../conexion.php';
$id_doc = isset($_POST['id_ctb_doc']) ? $_POST['id_ctb_doc'] : exit('Acceso no disponible');
$id_tercero = $_POST['idTercero'];
$id_crp = $_POST['id_crpp'];
$id_codigoCta = $_POST['id_codigoCta'];
$valorDebito = $_POST['valorDebito'] > 0 ? str_replace(",", "", $_POST['valorDebito']) : 0;
$valorCredito = $_POST['valorCredito'] > 0 ? str_replace(",", "", $_POST['valorCredito']) : 0;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$opcion = $_POST['opcion'];
$response = [];
//
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($opcion == 0) {
        $query = "INSERT INTO `ctb_libaux`
                        (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $id_tercero, PDO::PARAM_STR);
        $query->bindParam(3, $id_codigoCta, PDO::PARAM_STR);
        $query->bindParam(4, $valorDebito, PDO::PARAM_STR);
        $query->bindParam(5, $valorCredito, PDO::PARAM_STR);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
            exit();
        }
    } else {
        // Para editar el movimiento
        $query = "UPDATE `ctb_libaux` 
                SET `id_tercero_api` = ?,`id_cuenta` = ?,`debito` = ?,`credito` = ? 
                WHERE `id_ctb_libaux` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_tercero, PDO::PARAM_STR);
        $query->bindParam(2, $id_codigoCta, PDO::PARAM_STR);
        $query->bindParam(3, $valorDebito, PDO::PARAM_STR);
        $query->bindParam(4, $valorCredito, PDO::PARAM_INT);
        $query->bindParam(5, $opcion, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
            exit();
        } else {
            if ($query->rowCount() > 0) {
                $query = $query = "UPDATE `ctb_libaux` SET `id_user_act` = ?,`fecha_act` = ? WHERE `id_ctb_libaux` = ?";
                $query = $cmd->prepare($query);
                $query->bindValue(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2);
                $query->bindParam(3, $opcion, PDO::PARAM_INT);
                $query->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
        $cmd = null;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
