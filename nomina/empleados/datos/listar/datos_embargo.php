<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT *
            FROM
                nom_embargos
            INNER JOIN nom_juzgados 
                ON (nom_embargos.id_juzgado = nom_juzgados.id_juzgado)
            WHERE id_empleado = '$id'";
    $rs = $cmd->query($sql);
    $embargos = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT nom_liq_embargo.id_embargo, id_empleado, SUM(val_mes_embargo) AS pagado, COUNT(nom_liq_embargo.id_embargo) AS cuotas
            FROM
                nom_liq_embargo
            INNER JOIN nom_embargos
                ON (nom_liq_embargo.id_embargo = nom_embargos.id_embargo)";
    $rs = $cmd->query($sql);
    $pagosEmb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($embargos)) {
    foreach ($embargos as $e) {
        $idEmb = $e['id_embargo'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idEmb . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idEmb . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($e['estado'] == 0) {
            $borrar  = $editar = null;
        }
        $key = array_search($idEmb, array_column($pagosEmb, 'id_embargo'));
        if (false !== $key) {
            $pago = $pagosEmb[$key]['pagado'];
        } else {
            $pago = '0';
            $cuotas = '0';
        }
        $estado = $e['estado'] == 1 ? '<span class="badge badge-success">Activo</span><button value="' . $idEmb . '" class="btn btn-outline-success btn-sm btn-circle estado" title="Cambiar Estado" estado="' . $e['estado'] . '"><span class="fas fa-exchange-alt"></span></button>' : '<span class="badge badge-secondary">Inactivo</span><button value="' . $idEmb . '" class="btn btn-outline-secondary btn-sm btn-circle estado" title="Cambiar Estado"  estado="' . $e['estado'] . '"><span class="fas fa-exchange-alt"></span></button>';
        $data[] = [
            'id_embargo' => $e['id_embargo'],
            'juzgado' => $e['nom_juzgado'],
            'valor_total' => pesos($e['valor_total']),
            'val_mes' => pesos($e['valor_mes']),
            'val_pagado' => pesos($pago),
            'fecha_inicio' => $e['fec_inicio'],
            'fecha_fin' => $e['fec_fin'],
            'estado' => $estado,
            'botones' => '<div class="center-block">' . $editar . $borrar . '<button value="' . $idEmb . '" class="btn btn-outline-warning btn-sm btn-circle detalles" title="Detalles Embargo"><span value="' . $idEmb . '" class="far fa-eye fa-lg"></span></button></div>'
        ];
    }
} else {
    $data = [
        'id_embargo' => '',
        'juzgado' => '',
        'valor_total' => '',
        'porcentaje' => '',
        'val_mes' => '',
        'val_pagado' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
