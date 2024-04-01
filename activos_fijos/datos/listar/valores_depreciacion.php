<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `nom_meses`.`codigo`
                , `nom_meses`.`nom_mes`
                , `nom_meses`.`fin_mes`
                ,`t1`.`total_depreciado`
                ,`t1`.`fec_reg`
                
            FROM 
                (SELECT
                    `mes`
                    , `fec_reg`
                    , SUM(`val_depreciado`) AS `total_depreciado`
                FROM
                    `nom_liq_depreciacion`
                WHERE (`anio` = '$vigencia')
                GROUP BY `mes`) AS `t1`
            INNER JOIN `nom_meses` ON (`t1`.`mes` = `nom_meses`.`codigo`)";
    $rs = $cmd->query($sql);
    $depxmes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($depxmes)) {
    foreach ($depxmes as $dp) {
        $id_de = $dp['codigo'];
        $editar = null;
        if ((intval($permisos['listar'])) == 1) {
            $listar = '<a value="' . $id_de . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb ver" title="Actualizar o modificar"><span class="fas fa-eye fa-lg"></span></a>';
        }
        $data[] = [
            "nom_mes" => $dp['nom_mes'],
            "fin_mes" => $vigencia . '-' . $dp['codigo'] . '-' . $dp['fin_mes'],
            "fec_reg" => $dp['fec_reg'],
            "total" => '<div class="text-right">' . pesos($dp['total_depreciado']) . '</div>',
            "botones" => '<div class="text-center">' . $listar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
