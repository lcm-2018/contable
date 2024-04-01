<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_contrato_emp, tipo_contrato,descripcion, fec_inicio, fec_fin, no_documento, nombre1, nombre2, apellido1, apellido2, salario_basico
            FROM
                nom_contratos_empleados
            INNER JOIN nom_empleado 
                ON (nom_contratos_empleados.id_empleado = nom_empleado.id_empleado)
            INNER JOIN nom_salarios_basico 
                ON (nom_salarios_basico.id_empleado = nom_empleado.id_empleado)
            INNER JOIN nom_tipo_contrato 
                ON (nom_tipo_contrato.id_tip_contrato = nom_empleado.tipo_contrato)
            WHERE nom_salarios_basico.vigencia = '$vigencia' AND nom_contratos_empleados.estado = '0'";
    $rs = $cmd->query($sql);
    $contratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($contratos)) {
    foreach ($contratos as $ct) {
        $id_ct = $ct['id_contrato_emp'];
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_ct . '-' . $ct['tipo_contrato'] . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        } else {
            $editar = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ct . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'contrato' => 'CNE-' . $id_ct,
            'tipo' => mb_strtoupper($ct['descripcion']),
            'no_doc' => $ct['no_documento'],
            'nombre' => mb_strtoupper($ct['nombre1'] . ' ' . $ct['nombre2'] . ' ' . $ct['apellido1'] . ' ' . $ct['apellido2']),
            'fec_ini' => $ct['fec_inicio'],
            'fec_fin' => $ct['fec_fin'],
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
