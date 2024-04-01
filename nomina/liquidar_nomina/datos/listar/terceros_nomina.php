<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `t1`.`id_tercero_api` 
                , `t1`.`nombre` 
                , `t1`.`nit` 
                , `t1`.`categoria`
                , `nom_categoria_tercero`.`codigo`
                , `nom_categoria_tercero`.`descripcion`
            FROM 
                (SELECT
                    `id_tercero_api`, `nit` , `nombre_eps` AS `nombre`, 1 AS `categoria`
                FROM
                    `nom_epss`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nit_afp`, `nombre_afp`, 2 AS `categoria`
                FROM
                    `nom_afp`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nit_arl`, `nombre_arl`, 3 AS `categoria`
                FROM
                    `nom_arl`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nit_fc`, `nombre_fc`, 4 AS `categoria`
                FROM
                    `nom_fondo_censan`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nit_banco`, `nom_banco`, 5 AS `categoria`
                FROM
                    `tb_bancos`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nit`, `nom_juzgado`, 6 AS `categoria`
                FROM
                    `nom_juzgados`
                UNION ALL
                SELECT
                    `id_tercero_api`, `nom_sindicato`, `nit`, 7 AS `categoria`
                FROM
                    `nom_sindicatos`) AS `t1` 
            INNER JOIN `nom_categoria_tercero`
                ON (`t1`.`categoria` = `nom_categoria_tercero`.`id_cat`)";
    $rs = $cmd->query($sql);
    $terceros_nomina = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($terceros_nomina as $tn) {
    $datos[] = [
        'nombre' => $tn['nombre'],
        'nit' => $tn['nit'],
        'categoria' => $tn['categoria'],
        'codigo' => $tn['codigo'],
        'descripcion' => $tn['descripcion'],
    ];
}
$data = [
    'data' => $datos
];
echo json_encode($data);
