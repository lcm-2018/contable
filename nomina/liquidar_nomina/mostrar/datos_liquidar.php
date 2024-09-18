<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$mes = isset($_POST['mes']) ? $_POST['mes'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
            nom_licenciasmp
            WHERE id_empleado ='$id'";
    $rs = $cmd->query($sql);
    $licencias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../permisos.php';
if (!empty($licencias)) {
    foreach ($licencias as $l) {

        $data[] = [
            'check' => $idLic,
            'doc' => $l['fec_inicio'],
            'nombre' => $l['fec_fin'],
            'dias_lab' => $l['dias_inactivo'],
            'dias_incap' => $l['dias_habiles'],
            'dias_lic' => $l['dias_habiles'],
            'dias_vac' => $l['dias_habiles'],
            'forma_pago' => $l['dias_habiles'],
        ];
    }
} else {
    $data = [
        'check' => '',
        'doc' => '',
        'nombre' =>  '',
        'dias_lab' =>  '',
        'dias_incap' =>  '',
        'dias_lic' =>  '',
        'dias_vac' => '',
        'forma_pago' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
