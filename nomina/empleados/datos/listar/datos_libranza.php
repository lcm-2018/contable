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
    $sql = "SELECT 
                `nom_libranzas`.`id_libranza`
                , `nom_libranzas`.`id_banco`
                , `nom_libranzas`.`id_empleado`
                , `nom_libranzas`.`estado`
                , `nom_libranzas`.`descripcion_lib`
                , `nom_libranzas`.`valor_total`
                , `nom_libranzas`.`cuotas`
                , `nom_libranzas`.`val_mes`
                , `nom_libranzas`.`porcentaje`
                , `nom_libranzas`.`fecha_inicio`
                , `nom_libranzas`.`fecha_fin`
                , `tb_bancos`.`id_tercero_api`
                , `tb_bancos`.`nit_banco`
                , `tb_bancos`.`dig_ver`
                , `tb_bancos`.`cod_banco`
                , `tb_bancos`.`nom_banco`
            FROM
                nom_libranzas
            INNER JOIN tb_bancos 
                ON (nom_libranzas.id_banco = tb_bancos.id_banco) 
            WHERE id_empleado = '$id'";
    $rs = $cmd->query($sql);
    $libranzas = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT nom_liq_libranza.id_libranza, id_empleado, SUM(val_mes_lib) AS pagado, COUNT(nom_liq_libranza.id_libranza) AS cuotas
            FROM
                nom_liq_libranza
            INNER JOIN nom_libranzas 
                ON (nom_liq_libranza.id_libranza = nom_libranzas.id_libranza)
            GROUP BY id_libranza";
    $rs = $cmd->query($sql);
    $pagosLib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($libranzas)) {
    foreach ($libranzas as $li) {
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $li['id_libranza'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $li['id_libranza'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($li['estado'] == 0) {
            $borrar = $editar = null;
        }
        $idlib = $li['id_libranza'];
        $key = array_search($idlib, array_column($pagosLib, 'id_libranza'));
        if (false !== $key) {
            $pago = $pagosLib[$key]['pagado'];
            $cuotas = $pagosLib[$key]['cuotas'];
        } else {
            $pago = '0';
            $cuotas = '0';
        }
        //echo $li['estado'] . '<br>';
        $estado = $li['estado'] == 1 ? '<span class="badge badge-success">Activo</span><button value="' . $li['id_libranza'] . '" class="btn btn-outline-success btn-sm btn-circle estado" title="Cambiar Estado" estado="' . $li['estado'] . '"><span class="fas fa-exchange-alt"></span></button>' : '<span class="badge badge-secondary">Inactivo</span><button value="' . $li['id_libranza'] . '" class="btn btn-outline-secondary btn-sm btn-circle estado" title="Cambiar Estado"  estado="' . $li['estado'] . '"><span class="fas fa-exchange-alt"></span></button>';
        $data[] = [
            'id_libranza' => $li['id_libranza'],
            'nom_banco' => $li['nom_banco'],
            'valor_total' => pesos($li['valor_total']),
            'cuotas' => $li['cuotas'],
            'val_mes' => pesos($li['val_mes']),
            'val_pagado' => pesos($pago),
            'cuotas_pag' => $cuotas,
            'fecha_inicio' => $li['fecha_inicio'],
            'fecha_fin' => $li['fecha_fin'],
            'estado' => $estado,
            'botones' => '<div class="center-block">' . $editar . $borrar . '<button class="btn btn-outline-warning btn-sm btn-circle detalles" value="' . $idlib . '" title="Detalles Libranza"><span class="far fa-eye fa-lg"></span></button></div>'
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
