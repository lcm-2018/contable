<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$corte = isset($_POST['corte']) ? $_POST['corte'] : exit('Acción no permitida');
include '../../../../conexion.php';
include '../../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , `no_documento`
                , CONCAT_WS(' ',`nombre1`
                , `nombre2`
                , `apellido1`
                , `apellido2`) AS `nombre`
                , `fec_retiro`
            FROM
                `nom_empleado`
            WHERE `fec_retiro` <= '$corte' AND `fec_retiro` >= '2023-01-01'
                AND `id_empleado` NOT IN (SELECT `id_empleado` FROM `nom_empleados_retirados`) AND `estado` = 0
            ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`,`dias` FROM `nom_liq_compesatorio` WHERE `id_nomina` = 0";
    $rs = $cmd->query($sql);
    $dias_compensa = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($obj)) {
    foreach ($obj as $o) {
        $id_empleado = $o['id_empleado'];
        $dias = 0;
        $key = array_search($id_empleado, array_column($dias_compensa, 'id_empleado'));
        if ($key !== false) {
            $dias = $dias_compensa[$key]['dias'];
        }
        $data[] = [
            'check' => '<div class="text-center"><div class="form-check">
            <input class="form-check-input position-static" type="checkbox" name=id_empleado[] value="' . $id_empleado . '" checked>
          </div></div>',
            'no_doc' => $o['no_documento'],
            'nombre' => strtoupper($o['nombre']),
            'fec_termina' => $o['fec_retiro'],
            'compensatorio' => '<div class="text-center"><input type="number" class="form-control form-control-sm altura" name="compensatorio[' . $id_empleado . ']" value="' . $dias . '"></div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];
echo json_encode($datos);
