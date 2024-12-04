<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_cdp = $_POST['id_cdp'];
$id_vigencia = $_SESSION['id_vigencia'];
$vigencia = $_SESSION['vigencia'];
$id_pto = $_POST['id_pto'];
$id_adq = isset($_POST['id_adq']) ? $_POST['id_adq'] : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_cdp_detalle`.`id_pto_cdp_det` AS `id_detalle`
                , `pto_cdp_detalle`.`id_pto_cdp` AS `id_pto_doc`
                , `pto_cdp_detalle`.`valor` AS `valor_deb`
                , `pto_cdp_detalle`.`valor_liberado` AS `valor_cred`
                , `pto_cargue`.`id_cargue` AS `id_pto`
                , `pto_cargue`.`cod_pptal` AS `rubro`
                , `pto_cargue`.`nom_rubro` AS `nom_rubro`
            FROM
                `pto_cdp_detalle`
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
            WHERE (`pto_cdp_detalle`.`id_pto_cdp` = $id_cdp)";
    // Si documento es igual a TRA modificamos la consulta
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `estado` FROM `pto_cdp` WHERE (`id_pto_cdp` = $id_cdp)";
    // Si documento es igual a TRA modificamos la consulta
    $rs = $cmd->query($sql);
    $estado = $rs->fetch(PDO::FETCH_ASSOC);
    $estado = !empty($estado) ? $estado['estado'] : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_rb = 0;
$valor = 0;
$nom_rubro = '';
$tp_dt = 0;
if ($id_adq > 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    `ctt_escala_honorarios`.`cod_pptal` AS `id_rubro`
                    , `ctt_adquisiciones`.`val_contrato`
                    , `pto_cargue`.`cod_pptal`
                    , `pto_cargue`.`nom_rubro`
                    , `pto_cargue`.`tipo_dato`
                FROM
                    `ctt_adquisiciones`
                    LEFT JOIN `ctt_escala_honorarios` 
                    ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `ctt_escala_honorarios`.`id_tipo_b_s` AND `ctt_escala_honorarios`.`vigencia` = '$vigencia')
                    LEFT JOIN `pto_cargue` 
                    ON (`ctt_escala_honorarios`.`cod_pptal` = `pto_cargue`.`id_cargue`)
                WHERE (`ctt_adquisiciones`.`id_adquisicion` = $id_adq)";
        // Si documento es igual a TRA modificamos la consulta
        $rs = $cmd->query($sql);
        $adquisicion = $rs->fetch(PDO::FETCH_ASSOC);
        if (empty($listappto)) {
            if (!empty($adquisicion)) {
                $id_rb = $adquisicion['id_rubro'] != '' ? $adquisicion['id_rubro'] : 0;
                $valor = $adquisicion['val_contrato'] != '' ? $adquisicion['val_contrato'] : 0;
                $nom_rubro = $adquisicion['cod_pptal'] != '' ? $adquisicion['cod_pptal'] . ' - ' . $adquisicion['nom_rubro'] : '';
                $tp_dt = $adquisicion['tipo_dato'] != '' ? $adquisicion['tipo_dato'] : 0;
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else {
}
$suma = 0;
$resta = 0;
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $editar =  $borrar = $detalles = $acciones = null;
        $id_detalle = $lp['id_detalle'];
        $id_pto = $lp['id_pto_doc'];
        $debito = number_format($lp['valor_deb'], 2, ',', '.');
        $suma += $lp['valor_deb'];
        if ($estado < 2) {
            if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
                $editar = '<a value="' . $id_detalle . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar detalle"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                /*$detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra carga" href="#">Cargar2 presupuesto</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra modifica" href="#">Modificaciones</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="#">Ejecución</a>
            </div>';*/
            }
            if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
                $borrar = '<a value="' .  $id_detalle . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        $data[] = [
            'id' => $id_detalle,
            'rubro' => $lp['rubro'] . ' - ' . $lp['nom_rubro'],
            'valor' => '<div class="text-right">' . $debito . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $acciones . $borrar . '</div>',

        ];
    }
}
$suma = number_format($suma, 2, ',', '.');
if ($estado == '1') {
    $rubro = ' <input type="text" id="rubroCod" class="form-control form-control-sm" value="' . $nom_rubro . '">
            <input type="hidden" name="id_rubroCod" id="id_rubroCod" class="form-control form-control-sm" value="' . $id_rb . '">
            <input type="hidden" id="tipoRubro" name="tipoRubro" value="' . $tp_dt . '">';
    $debito = '<input type="text" name="valorDeb" id="valorDeb" class="form-control form-control-sm " size="6" value="' . $valor . '" style="text-align: right;" onkeyup="valorMiles(id)">';
    $botones = '<input type="hidden" name="id_pto_mod" id="id_pto_mod" value="' . $id_cdp . '">
            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Ver historial del rubro" onclick="verHistorial(this)"><span class="far fa-list-alt fa-lg"></span></a>
            <button text="0" class="btn btn-primary btn-sm" onclick="RegDetalleCDPs(this)">Agregar</button>';
    $data[] = [
        'id' => '2',
        'rubro' => $rubro,
        'valor' => '<div class="text-right">' . $debito . '</div>',
        'botones' => '<div class="text-center">' . $botones . '</div>',
    ];
}
$data[] = [
    'id' => '1',
    'rubro' => '<div class="text-center"><b>TOTAL</b></div>',
    'valor' => '<div class="text-right">' . $suma . '</div>',
    'botones' => '<div class="text-center"></div>',

];
$datos = ['data' => $data];

echo json_encode($datos);
