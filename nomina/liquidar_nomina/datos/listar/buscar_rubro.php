<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$buscar = mb_strtoupper($_POST['term']);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_cargue`.`id_cargue`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_tipo` = 2
                AND `pto_presupuestos`.`id_vigencia` = 8
                AND (`pto_cargue`.`cod_pptal` LIKE '%$buscar%'
                OR `pto_cargue`.`nom_rubro` LIKE '%$buscar%'))";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($rubros)) {
    foreach ($rubros as $r) {
        $nom_rubro = $r['cod_pptal'] . ' -> ' . $r['nom_rubro'];
        $data[] = [
            'id' => $r['id_cargue'],
            'label' => $nom_rubro,
            'tipo' => $r['tipo_dato'],
        ];
    }
} else {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
        'tipo' => '0',
    ];
}
echo json_encode($data);
