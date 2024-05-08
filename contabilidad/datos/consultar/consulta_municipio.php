<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    // Consulta del muncipio asociado a la empresa
    $sql = "SELECT
                `tb_sedes`.`nom_sede`
                , `tb_municipios`.`nom_municipio`
                , `tb_sedes`.`id_municipio`
                , `tb_sedes`.`id_sede`
            FROM
                `tb_sedes`
                INNER JOIN `tb_municipios` 
                    ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
            WHERE (`tb_municipios`.`nom_municipio` LIKE '%$search%')";
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
