<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    // Consulta del muncipio asociado a la empresa
    $sq1 = "SELECT `id_dpto` FROM `tb_datos_ips`;";
    $re1 = $conexion->query($sq1);
    $row1 = $re1->fetch_assoc();
    $id_dpto = $row1['id_dpto'];

    $sql = "SELECT id_municipio,codigo_municipio,nom_municipio FROM tb_municipios WHERE id_departamento =$id_dpto AND (codigo_municipio LIKE '$search%' OR nom_municipio LIKE '$search%')";
    $res = $conexion->query($sql);
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $response[] = array("value" => $row['id_municipio'], "label" => $row['nom_municipio']);
        }
    } else {
        $response[] = array("value" => "0", "label" => "No se encontraron resultados...");
    }
    echo json_encode($response);
}
exit;
