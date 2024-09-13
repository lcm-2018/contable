<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
function pesos($valor)
{
    return number_format($valor, 2, '.', ',');
}
$id_detalle = isset($_POST['id']) ?  $_POST['id'] : exit('Acceso no disponible');
try {
    $sql = "SELECT
                `ctb_libaux`.`id_ctb_libaux`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_libaux`.`id_cuenta`
                , `ctb_pgcp`.`tipo_dato`
            FROM
                `ctb_libaux`
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE (`ctb_libaux`.`id_ctb_libaux` = $id_detalle)";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t[] = $detalle['id_tercero_api'];
$ids = implode(',', $id_t);
$terceros = getTerceros($ids, $cmd);
$cmd = null;
$res['status'] = 'error';
if (!empty($detalle)) {
    $tercero = ltrim($terceros[0]['nom_tercero'] .' -> ' . $terceros[0]['nit_tercero']);
    $res['status'] = 'ok';
    $res[1] = '<input type="text" id="codigoCta" name="codigoCta" class="form-control form-control-sm" value="' .  $detalle['cuenta'] . ' - ' . $detalle['nombre'] . '">
            <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="' . $detalle['id_cuenta'] . '">
            <input type="hidden" id="tipoDato" name="tipoDato" value="' . $detalle['tipo_dato'] . '">';
    $res[2] = '<input type="text" name="bTercero" id="bTercero" class="form-control form-control-sm bTercero" value="' . $tercero . '">
            <input type="hidden" name="idTercero" id="idTercero" value="' . $detalle['id_tercero_api'] . '">';
    $res[3] = '<input type="text" name="valorDebito" id="valorDebito" class="form-control form-control-sm " style="text-align: right;" onkeyup="valorMiles(id)" onchange="llenarCero(id)" value="' . pesos($detalle['debito']) . '">';
    $res[4] = '<input type="text" name="valorCredito" id="valorCredito" class="form-control form-control-sm " style="text-align: right;" onkeyup="valorMiles(id)" onchange="llenarCero(id)" value="' . pesos($detalle['credito']) . '">';
    $res[5] = '<div class="text-center"><button text="' . $id_detalle . '" class="btn btn-primary btn-sm" onclick="GestMvtoDetalle(this)">Modificar</button></div>';
    $res['msg'] = 'Consulta exitosa';
} else {
    $res['msg'] = 'No se encontraron datos';
}
echo json_encode($res);
