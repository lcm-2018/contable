<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
// Incio la transaccion
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                SUM(`debito`) as `debito`, SUM(`credito`) as `credito` 
            FROM 
                `ctb_libaux` 
            WHERE (`id_ctb_doc`= $data)";
    $rs = $cmd->query($sql);
    $sumaMov = $rs->fetch();
    $dif = $sumaMov['debito'] - $sumaMov['credito'];

    $sql = "SELECT
                `id_cuenta`, `id_ctb_doc`
            FROM
                `ctb_libaux`
            WHERE (`id_ctb_doc` = $data)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    if ($sumaMov['debito'] == 0 || $sumaMov['credito'] == 0) {
        $dif = 3;
    }
    foreach ($cuentas as $rp) {
        if ($rp['id_cuenta'] == '') {
            $dif = 3;
            break;
        }
    }
    if ($dif == 0) {
        $estado = 2;
        $query = "UPDATE `ctb_doc` SET `estado`= ? WHERE `id_ctb_doc`= ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $estado, PDO::PARAM_INT);
        $query->bindParam(2, $data, PDO::PARAM_INT);
        $query->execute();
        $response['status'] = 'ok';
    } else {
        $response['msg'] = 'Error en el movimiento contable';
    }
    $cmd = null;
} catch (Exception $e) {
    $response['msg'] = $e->getMessage();
}
echo json_encode($response);
