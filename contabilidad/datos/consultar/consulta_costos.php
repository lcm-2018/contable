<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `far_centrocosto_area`.`id_area`
                , `tb_centrocostos`.`nom_centro`
            FROM
                `far_centrocosto_area`
                LEFT JOIN `tb_centrocostos` 
                    ON (`far_centrocosto_area`.`id_centrocosto` = `tb_centrocostos`.`id_centro`)
            WHERE (`far_centrocosto_area`.`id_sede` = {$_post['id']} AND `far_centrocosto_area`.`id_area` > 0)
            GROUP BY `far_centrocosto_area`.`id_centrocosto`
            ORDER BY `tb_centrocostos`.`nom_centro` ASC";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '<label for="id_cc" class="small">CENTRO DE COSTO</label>
<select class="form-control form-control-sm py-0 sm" id="id_cc" name="id_cc" >
<option value="0">-- Seleccionar --</option>';
foreach ($centros as $sed) {
    $response .= '<option value="' . $sed['id_area'] . '">' . $sed['nom_centro'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
