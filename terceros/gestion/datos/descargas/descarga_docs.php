<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../../index.php");
    exit();
}

include '../../../../conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT
                    `ruta_doc`,`nombre_doc`
                FROM `bd_cronhis`.`ctt_documentos`
                WHERE `id_soportester` = $id";
        $rs = $cmd->query($sql);
        $pdf = $rs->fetch(PDO::FETCH_ASSOC);

        if (!empty($pdf)) {
            $filePath = '../' . $pdf['ruta_doc'] . $pdf['nombre_doc'];
            if (file_exists($filePath)) {
                // Forzar descarga
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            } else {
                echo "Archivo no encontrado.";
            }
        } else {
            echo "Registro no encontrado.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ID no proporcionado.";
}
