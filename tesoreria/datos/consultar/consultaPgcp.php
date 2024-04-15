<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    $presupuesto = mysqli_real_escape_string($conexion, $_POST['valor']);
    $sql = "SELECT cuenta,nombre,tipo_dato FROM ctb_pgcp WHERE cuenta LIKE '$search%'";
    $res = $conexion->query($sql);
    while ($row = $res->fetch_assoc()) {
        $response[] = array("value" => $row['cuenta'], "label" => $row['cuenta'] . " - " . $row['nombre'], "tipo" => $row['tipo_dato']);
    }
    echo json_encode($response);
}

exit;
