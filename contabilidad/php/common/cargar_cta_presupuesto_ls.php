<?php
session_start();
if (!isset($_SESSION['user'])) {
<<<<<<< HEAD
    echo '<script>window.location.replace("../../../index.php");</script>';
=======
    header("Location: ../../../index.php");
>>>>>>> d750d9bf66c1ebfb0ab684f97d76cc2d83a9799b
    exit();
}
include '../../../conexion.php';

$usuario = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];

$term = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT pto_cargue.id_cargue AS id_cta,pto_cargue.tipo_dato AS tipo,
                CONCAT_WS(' - ',pto_cargue.cod_pptal,pto_cargue.nom_rubro) AS nom_cta
            FROM pto_cargue
            INNER JOIN pto_presupuestos ON (pto_presupuestos.id_pto=pto_cargue.id_pto)
            INNER JOIN tb_vigencias ON (tb_vigencias.id_vigencia=pto_presupuestos.id_vigencia)
            WHERE pto_presupuestos.id_tipo=1 AND tb_vigencias.anio='" . $vigencia . "' AND CONCAT(pto_cargue.cod_pptal,pto_cargue.nom_rubro) LIKE '%$term%'
            ORDER BY pto_cargue.cod_pptal";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($objs as $obj) {
    $data[] = [
        "id" => $obj['id_cta'],
        "label" => $obj['nom_cta'],
        "tipo" => $obj['tipo']
    ];
}

if (empty($data)) {
    $data[] = [
        "id" => '',
        "label" => 'No hay coincidencias...',
        "tipo" => ''
    ];
}
echo json_encode($data);
