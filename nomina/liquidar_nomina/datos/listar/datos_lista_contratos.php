<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_contrato_emp, nom_empleado.id_empleado, nom_contratos_empleados.estado, fec_inicio, fec_fin, no_documento, CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) AS nombre
            FROM
                nom_contratos_empleados
            INNER JOIN nom_empleado 
                ON (nom_contratos_empleados.id_empleado = nom_empleado.id_empleado)
            WHERE nom_contratos_empleados.estado = '0' AND tipo_contrato <>  '2'";
    $rs = $cmd->query($sql);
    $lcontratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($lcontratos)) {
    foreach ($lcontratos as $lc) {
        $data[] = [
            'check' => '<div class="text-center listado"><input type="checkbox" name="check[]" checked value="' . $lc['id_contrato_emp'] . '"></div>',
            'no_contrato' => 'CNE-' . $lc['id_contrato_emp'],
            'no_doc' => $lc['no_documento'],
            'nombre' => mb_strtoupper($lc['nombre']),
            'fec_inicio' => $lc['fec_inicio'],
            'fec_termina' => $lc['fec_fin'],
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
