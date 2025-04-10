<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$val_busca = $_POST['search']['value'] ?? '';
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];
$where = '';
if ($val_busca != '') {
    $val_busca = trim($val_busca);
    $where = "AND (`nom_conceptosxvigencia`.`concepto` LIKE '%$val_busca%' OR `nom_valxvigencia`.`valor` LIKE '%$val_busca%')";
}

$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_valxvigencia`.`id_concepto`
                , `nom_conceptosxvigencia`.`concepto`
                , `nom_valxvigencia`.`valor`
                , `tb_vigencias`.`anio`
                , `nom_valxvigencia`.`id_valxvig`
            FROM
                `nom_valxvigencia`
                INNER JOIN `nom_conceptosxvigencia` 
                    ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE (`tb_vigencias`.`anio` = '$vigencia' $where)
            ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `nom_valxvigencia`
                INNER JOIN `nom_conceptosxvigencia` 
                    ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE (`tb_vigencias`.`anio` = '$vigencia' $where)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                COUNT(*) AS `total`
            FROM
                `nom_valxvigencia`
                INNER JOIN `nom_conceptosxvigencia` 
                    ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    // contar el total de registros
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$datos = [];
foreach ($conceptos as $cp) {
    $id = $cp['id_valxvig'];
    if (PermisosUsuario($permisos, 5114, 3) || $id_rol == 1) {
        $actualizar = '<button data-id="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb actualizar" title="Actualizar valor concepto"><span class="fas fa-pencil-alt fa-lg"></span></button>';
    }
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<button data-id="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar concepto"><span class="fas fa-trash-alt fa-lg"></span></button>';
    }
    $datos[] = array(
        'id' => $cp['id_concepto'],
        'concepto' => mb_strtoupper($cp['concepto']),
        'valor' => '<div class="text-right">' . pesos($cp['valor']) . '</div>',
        'botones' => '<div class="text-center">' . $actualizar . $eliminar . '</div>'
    );
}
$data = [
    'data' => $datos,
    'recordsFiltered' => $totalRecordsFilter,
    'recordsTotal' => $totalRecords,
];
echo json_encode($data);
