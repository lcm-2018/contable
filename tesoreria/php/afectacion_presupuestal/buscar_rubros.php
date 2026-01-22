<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                pto_cargue.id_cargue
                , pto_cargue.cod_pptal
                , pto_cargue.nom_rubro
                , pto_presupuestos.id_tipo
                , pto_cargue.tipo_dato
                , tb_vigencias.anio
            FROM
                pto_cargue
                INNER JOIN pto_presupuestos ON (pto_cargue.id_pto = pto_presupuestos.id_pto)
                INNER JOIN tb_vigencias ON (pto_presupuestos.id_vigencia = tb_vigencias.id_vigencia)
            WHERE pto_presupuestos.id_tipo=1";
    $rs = $cmd->query($sql);
    $obj_rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
$buscar = mb_strtoupper($_POST['term']);
if ($buscar == '%%') {
    foreach ($obj_rubros as $obj) {
        $nom_rubro = mb_strtoupper($obj['cod_pptal']) . ' -> ' . $obj['nom_rubro'];
        $data[] = [
            'id' => $obj['id_cargue'],
            'label' => $nom_rubro,
            'tipo_dato' => $obj['tipo_dato'],
            'anio' => $obj['anio'],
        ];
    }
} else {
    foreach ($obj_rubros as $obj) {
        $nom_rubro = mb_strtoupper($obj['cod_pptal']) . ' -> ' . $obj['nom_rubro'];
        $pos = strpos($nom_rubro, $buscar);
        if ($pos !== false) {
            $data[] = [
                'id' => $obj['id_cargue'],
                'label' => $nom_rubro,
                'tipo_dato' => $obj['tipo_dato'],
                'anio' => $obj['anio'],
            ];
        }
    }
}

if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
        'tipo_dato' => '0',
        'anio' => '1999',
    ];
}
echo json_encode($data);
