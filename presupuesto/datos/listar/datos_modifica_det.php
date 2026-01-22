<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_pto_mod = $_POST['id_pto_mod'];
$id_vigencia = $_SESSION['id_vigencia'];
$id_pto = $_POST['id_pto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_mod_detalle`.`id_pto_mod_det` as `id_detalle`
                , `pto_mod_detalle`.`id_pto_mod` as `id_pto_doc`
                , `pto_mod_detalle`.`valor_deb`
                , `pto_mod_detalle`.`valor_cred`
                , `pto_mod_detalle`.`id_cargue` as id_pto
                , `pto_cargue`.`cod_pptal` as rubro
                , `pto_cargue`.`nom_rubro` as nom_rubro
                , `pto_presupuestos`.`id_tipo`
            FROM
                `pto_mod_detalle`
                INNER JOIN `pto_cargue` 
                    ON (`pto_mod_detalle`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_mod_detalle`.`id_pto_mod` = $id_pto_mod)";
    // Si documento es igual a TRA modificamos la consulta
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `estado`, `id_tipo_mod` FROM `pto_mod` WHERE (`id_pto_mod` = $id_pto_mod)";
    // Si documento es igual a TRA modificamos la consulta
    $rs = $cmd->query($sql);
    $status = $rs->fetch();
    $estado = empty($status) ? 1 : $status['estado'];
    $tp_mod = $status['id_tipo_mod'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo` FROM `pto_presupuestos` WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetch();
    $tipo = $tipo['id_tipo'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$valida = false;
if ($tp_mod == 2 || $tp_mod == 3) {
    $valida = true;
}
if ($valida && $tipo == 1) {
    $ingresos = '';
    $gastos = 'disabled';
} else if ($valida && $tipo == 2) {
    $ingresos = 'disabled';
    $gastos = '';
} else {
    $ingresos = '';
    $gastos = '';
}
$suma = 0;
$resta = 0;
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $id_detalle = $lp['id_detalle'];
        $id_pto = $lp['id_pto_doc'];
        $debito = number_format($lp['valor_deb'], 2, ',', '.');
        $credito = number_format($lp['valor_cred'], 2, ',', '.');
        if ($valida && $lp['id_tipo'] == 2) {
            $debito = number_format(0, 2, ',', '.');
            $credito = number_format($lp['valor_deb'], 2, ',', '.');
        }
        $suma += $lp['valor_deb'];
        $resta += $lp['valor_cred'];
        if ($valida && $lp['id_tipo'] == 2) {
            $suma -= $lp['valor_deb'];
            $resta += $lp['valor_deb'];
        }
        $detalles = $acciones = null;
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_detalle . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar detalle"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        } else {
            $editar = null;
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' .  $id_detalle . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'id' => $id_detalle,
            'rubro' => $lp['rubro'] . ' - ' . $lp['nom_rubro'],
            'valor' => '<div class="text-right">' . $debito . '</div>',
            'valor2' => '<div class="text-right">' . $credito . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . '</div>',

        ];
    }
}
$queda = $suma - $resta;
$valor = '<input type="hidden" id="valida" value="' . $queda . '">';
if ($queda != 0) {
    $msg = '<span class="badge badge-danger">INCORRECTO</span>';
} else {
    $msg = '<span class="badge badge-success">CORRECTO</span>';
}
$suma = number_format($suma, 2, ',', '.');
$resta = number_format($resta, 2, ',', '.');
if ($estado == '1') {
    $rubro = ' <input type="text" id="rubroCod" class="form-control form-control-sm" value="">
            <input type="hidden" name="id_rubroCod" id="id_rubroCod" class="form-control form-control-sm" value="0">
            <input type="hidden" id="tipoRubro" name="tipoRubro" value="0">';
    $debito = '<input type="text" ' . $ingresos . ' name="valorDeb" id="valorDeb" class="form-control form-control-sm " size="6" value="0" style="text-align: right;" onkeyup="valorMiles(id)">';
    $credito = '<input type="text" ' . $gastos . ' name="valorCred" id="valorCred" class="form-control form-control-sm " size="6" value="0" style="text-align: right;" onkeyup="valorMiles(id)">';
    $botones = '<input type="hidden" name="id_pto_mod" id="id_pto_mod" value="' . $id_pto_mod . '">
            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Ver historial del rubro" onclick="verHistorial(this)"><span class="far fa-list-alt fa-lg"></span></a>
            <button text="0" class="btn btn-primary btn-sm" onclick="RegDetalleMod(this)">Agregar</button>';
    $data[] = [
        'id' => '2',
        'rubro' => $rubro,
        'valor' => '<div class="text-right">' . $debito . '</div>',
        'valor2' => '<div class="text-right">' . $credito . '</div>',
        'botones' => '<div class="text-center">' . $botones . '</div>',
    ];
}
$data[] = [
    'id' => '1',
    'rubro' => '<div class="text-center"><b>TOTAL</b></div>',
    'valor' => '<div class="text-right">' . $suma . '</div>',
    'valor2' => '<div class="text-right">' . $resta . '</div>',
    'botones' => '<div class="text-center">' . $msg . $valor . '</div>',

];
$datos = ['data' => $data];

echo json_encode($datos);
