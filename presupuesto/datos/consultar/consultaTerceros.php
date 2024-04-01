<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    $presupuesto = mysqli_real_escape_string($conexion, $_POST['valor']);
    $sql = "SELECT num_id,nombre FROM z_terceros WHERE num_id LIKE '$search%' OR nombre LIKE '$search%'";
    $res = $conexion->query($sql);
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $response[] = array("value" => $row['num_id'], "label" => $row['num_id'] . " - " . $row['nombre']);
        }
    } else {
        $response[] = array("value" => "0", "label" => "No se encontraron resultados...");
    }
    echo json_encode($response);
}
exit;
