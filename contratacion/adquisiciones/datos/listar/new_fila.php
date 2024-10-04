<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nom_sede` AS `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$res = '';
$opc = '';
foreach ($sedes as $s) {
    $opc .=  '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
}
$res =
    '<div class="form-row px-4">
    <div class="form-group col-md-4 mb-2">
        <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC">
            <option value="0">--Seleccione--</option>' .
    $opc . '
        </select>
    </div>
    <div class="form-group col-md-4 mb-2">
        <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto">
            <option value="0">--Seleccionar Sede--</option>
        </select>
    </div>
    <div class="form-group col-md-4 mb-2">
        <div class="input-group input-group-sm">
            <input type="number" name="numHorasMes[]" class="form-control">
            <div class="input-group-append">
                <button class="btn btn-outline-danger delRowSedes" type="button"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    </div>
</div>';
echo $res;
