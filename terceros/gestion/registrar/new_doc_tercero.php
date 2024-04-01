<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
if (isset($_FILES['fileDoc'])) {
    include '../../../conexion.php';
    $idt = $_POST['idTercero'];
    $ruta = '../../../uploads/terceros/docs/' . $idt.'/';
    if (!file_exists($ruta)) {
        $ruta = mkdir('../../../uploads/terceros/docs/' . $idt.'/', 0777,true);
        $ruta = $ruta = '../../../uploads/terceros/docs/' . $idt.'/';
    }
    $tipodoc = $_POST['slcTipoDocs'];
    $fini = date('Y-m-d', strtotime($_POST['datFecInicio']));
    $fvig = date('Y-m-d', strtotime($_POST['datFecVigencia']));
    $iduser = isset($_SESSION['user']) ? $_SESSION['id_user'] : $_SESSION['id_otro'];
    $tipuser = isset($_SESSION['user']) ? 'user' : 'otro';
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $nom_archivo = $tipodoc . '_' . date('YmdGis') . '_' . $_FILES['fileDoc']['name'];
    $nom_archivo = strlen($nom_archivo) >= 101 ? substr($nom_archivo, 0, 100) : $nom_archivo;
    $temporal = $_FILES['fileDoc']['tmp_name'];
    if (move_uploaded_file($temporal, $ruta . $nom_archivo)) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO seg_docs_tercero(id_tercero, id_tipo_doc, fec_inicio, fec_vig, ruta_doc, nombre_doc, id_user_reg, tipo_user_reg, fec_reg)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idt, PDO::PARAM_INT);
        $sql->bindParam(2, $tipodoc, PDO::PARAM_INT);
        $sql->bindParam(3, $fini, PDO::PARAM_STR);
        $sql->bindParam(4, $fvig, PDO::PARAM_STR);
        $sql->bindParam(5, $ruta, PDO::PARAM_STR);
        $sql->bindParam(6, $nom_archivo, PDO::PARAM_STR);
        $sql->bindParam(7, $iduser, PDO::PARAM_INT);
        $sql->bindParam(8, $tipuser, PDO::PARAM_STR);
        $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    } else {
        echo 'No se pudo adjuntar el archivo';
    }
} else {
    echo 'No se ha adjuntado ningún archivo';
}
