<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    $presupuesto = mysqli_real_escape_string($conexion, $_POST['valor']);
    $sql = "SELECT cod_pptal,nom_rubro,tipo_dato FROM pto_cargue WHERE id_pto_presupuestos='$presupuesto' AND (cod_pptal LIKE '$search%' OR nom_rubro LIKE '%$search%') ";
    $res = $conexion->query($sql);
    while ($row = $res->fetch_assoc()) {
        $response[] = array("value" => $row['cod_pptal'], "label" => $row['cod_pptal'] . " - " . $row['nom_rubro'], "tipo" => $row['tipo_dato']);
    }
    echo json_encode($response);
}

exit;
