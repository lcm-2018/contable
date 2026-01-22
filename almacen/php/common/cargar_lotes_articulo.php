<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/funciones_generales.php';

$id_articulo = $_POST['id_articulo'];
$id_bodega = $_POST['id_bodega'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    echo '<option value="" selected="selected"></option>';    
    
    $sql = "SELECT far_medicamento_lote.id_lote,IF(fec_vencimiento='3000-01-01',lote,CONCAT(lote,' [Fv:',fec_vencimiento,']')) AS nom_lote,
                far_medicamentos.nom_medicamento AS nom_articulo,
                far_medicamento_lote.id_presentacion,far_presentacion_comercial.nom_presentacion,
                IFNULL(far_presentacion_comercial.cantidad,1) AS cantidad_umpl,
                far_medicamentos.val_promedio
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_lote.id_presentacion)
            WHERE far_medicamento_lote.id_med=$id_articulo AND far_medicamento_lote.id_bodega=$id_bodega AND 
                    far_medicamento_lote.estado=1 AND far_medicamentos.estado=1 AND
                    far_medicamento_lote.fec_vencimiento>='" . date('Y-m-d') . "'
            ORDER BY far_medicamento_lote.id_lote DESC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    foreach ($objs as $obj) {
        $dtad = 'data-nom_articulo="' . $obj['nom_articulo'] . '"' . 
                'data-id_presentacion="' . $obj['id_presentacion'] . '"' .
                'data-nom_presentacion="' . $obj['nom_presentacion'] . '"' .
                'data-cantidad_umpl="' . $obj['cantidad_umpl'] . '"' .
                'data-val_promedio="' . formato_decimal($obj['val_promedio']) . '"';

        echo '<option value="' . $obj['id_lote'] . '"' . $dtad . '>' . $obj['nom_lote'] . '</option>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
