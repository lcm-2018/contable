<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`
                , `seg_terceros`.`tipo_doc`
                , `seg_terceros`.`no_doc`
                , `seg_terceros`.`estado`
                , `tb_tipo_tercero`.`descripcion`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
                INNER JOIN `tb_tipo_tercero` 
                    ON (`tb_rel_tercero`.`id_tipo_tercero` = `tb_tipo_tercero`.`id_tipo`)";
    $rs = $cmd->query($sql);
    $terEmpr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ced = '0';
foreach ($terEmpr as $tE) {
    $ced .= ',' . $tE['no_doc'];
}
//API URL
$url = $api . 'terceros/datos/res/lista/' . $ced;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
$data = [];
$buscar = mb_strtoupper($_POST['term']);
if ($buscar == '%%') {
    foreach ($terceros as $s) {
        $nom_tercero = mb_strtoupper($s['apellido1'] . ' ' . $s['apellido2'] . ' ' . $s['nombre1'] . ' ' . $s['nombre2'] . $s['razon_social'] . ' || ' . $s['cc_nit']);
        $data[] = [
            'id' => $s['id_tercero'],
            'label' => $nom_tercero,
        ];
    }
} else {
    foreach ($terceros as $s) {
        $nom_tercero = mb_strtoupper($s['apellido1'] . ' ' . $s['apellido2'] . ' ' . $s['nombre1'] . ' ' . $s['nombre2'] . $s['razon_social'] . ' || ' . $s['cc_nit']);
        $pos = strpos($nom_tercero, $buscar);
        if ($pos !== false) {
            $data[] = [
                'id' => $s['id_tercero'],
                'label' => $nom_tercero,
            ];
        }
    }
}

if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
